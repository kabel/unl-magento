<?php
/**
 * Inventory purchase model
 *
 * @method int getProductId getProductId()
 * @method Unl_Inventory_Model_Purchase setProductId setProductId(int $value)
 * @method float getQtyOnHand getQtyOnHand()
 * @method Unl_Inventory_Model_Purchase setQtyOnHand setQtyOnHand(float $value)
 * @method float getAmountRemaining getAmountRemaining()
 * @method Unl_Inventory_Model_Purchase setAmountRemaining setAmountRemaining(float $value)
 * @method string getCreatedAt getCreatedAt()
 * @method Unl_Inventory_Model_Purchase setCreatedAt setCreatedAt(string $value)
 * @method float getQty getQty()
 * @method Unl_Inventory_Model_Purchase setQty setQty(float $value)
 * @method float getAmount getAmount()
 * @method Unl_Inventory_Model_Purchase setAmount setAmount(float $value)
 * @method bool getCheckBackorders getCheckBackorders()
 * @method Unl_Inventory_Model_Purchase setCheckBackorders setCheckBackorders(bool $value)
 * @method bool getTryPublish getTryPublish()
 * @method Unl_Inventory_Model_Purchase setTryPublish setTryPublish(bool $value)
 * @method bool getStopAutoAudit getStopAutoAudit()
 * @method Unl_Inventory_Model_Purchase setStopAutoAudit setStopAutoAudit(bool $value)
 * @method bool getForcePublish getForcePublish()
 * @method Unl_Inventory_Model_Purchase setForcePublish setForcePublish(bool $value)
 *
 * @category Unl
 * @package Unl_Inventory
 * @author Kevin Abel <kabel2@unl.edu>
 *
 */
class Unl_Inventory_Model_Purchase extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'unl_inventory_purchase';

    protected $_eventObject = 'purchase';

    protected $_audits;

    protected function _construct()
    {
        $this->_init('unl_inventory/purchase');
    }

    protected function _beforeSave()
    {
        if ($this->isObjectNew()) {
            if (null === $this->getCreatedAt()) {
                $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
            }

            $this->setCheckBackorders(true);
            $this->setTryPublish(true);

            $this->setQtyOnHand($this->getQty());
            $this->setAmountRemaining($this->getAmount());

            if ($this->getStopAutoAudit() !== true) {
                $audit = Mage::getModel('unl_inventory/audit');
                $audit->setData(array(
                    'product' => $this->getProduct(),
                    'product_id' => $this->getProductId(),
                    'qty' => $this->getQty(),
                    'amount' => $this->getAmount(),
                    'created_at' => $this->getCreatedAt(),
                    'note' => $this->getNote(),
                ));

                if ($this->getAmount() > 0) {
                    $audit->setType(Unl_Inventory_Model_Audit::TYPE_PURCHASE);
                } else {
                    $audit->setType(Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT);
                }

                $this->addAudit($audit);
            }
        }

        return parent::_beforeSave();
    }

    protected function _reduceByBackorders()
    {
        if ($this->getId() === null) {
            return $this;
        }

        if ($this->getQtyOnHand() > 0) {
            $costPerItem = $this->getCostPerItem();
            $backorders = Mage::getResourceModel('unl_inventory/backorder_collection')
                ->addProductFilter($this->getProductId());

            if ($backorders->getSize()) {
                foreach ($backorders as $backorder) {
                    if ($backorder->getQty() < $this->getQtyOnHand()) {
                        $qtyChange = $backorder->getQty();
                        $backorder->handlePurchase($this, $qtyChange, $qtyChange * $costPerItem);
                        $this->setQtyOnHand($this->getQtyOnHand() - $qtyChange);
                        $this->setAmountRemaining($this->getAmountRemaining() - ($qtyChange * $costPerItem));
                    } else {
                        $backorder->handlePurchase($this, $this->getQtyOnHand(), $this->getAmountRemaining());
                        $this->setQtyOnHand(0);
                        $this->setAmountRemaining(0);
                        break;
                    }
                }
            }
        }

        return $this;
    }

    protected function _afterSave()
    {
        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();

        if ($this->getCheckBackorders()) {
            $this->_reduceByBackorders();
        }

        if (!empty($this->_audits)) {
            /* @var $audit Unl_Inventory_Model_Audit */
            foreach ($this->_audits as $audit) {
                if (!$audit->hasPurchase()) {
                    $audit->setPurchaseAssociations(array(
                        array('purchase' => $this, 'qty' => $this->getQty())
                    ));
                }

                $audit->save();

                if ($this->getReSyncAudits()) {
                    $audit->syncItemCost();
                }
            }
        }

        if ($this->getTryPublish() && $this->canPublish()) {
            if ($this->getForcePublish() || in_array($accounting, array(
                Unl_Inventory_Model_Config::ACCOUNTING_LIFO,
                Unl_Inventory_Model_Config::ACCOUNTING_AVG
            ))) {
                $this->_publish();
            }
        }

        return parent::_afterSave();
    }

    public function getAuditCollection()
    {
        if (empty($this->_audits)) {
            $this->_audits = Mage::getResourceModel('unl_inventory/audit_collection')
                ->addPurchaseFilter($this->getId());

            if ($this->getId()) {
                foreach ($this->_audits as $audit) {
                    $audit->setPurchase($this);
                }
            }
        }

        return $this->_audits;
    }

    public function addAudit(Unl_Inventory_Model_Audit $audit)
    {
        if (!$audit->getId()) {
            $this->getAuditCollection()->addItem($audit);
        }

        $this->_hasDataChanges = true;

        return $this;
    }

    public function canPublish()
    {
        return $this->getQtyOnHand() > 0;
    }

    protected function _publish()
    {
        $cost = $this->getCostPerItem();
        $this->getProduct()
            ->setCostFlag(true)
            ->setCost($cost)
            ->save();

        return $this;
    }

    public function getProduct()
    {
        if (!$this->hasProduct() && $this->hasProductId()) {
            $this->setProduct(Mage::getModel('catalog/product')->load($this->getProductId()));
        }

        return $this->getData('product');
    }

    public function getCostPerItem()
    {
        $cost = 0;
        if ($this->getQtyOnHand()) {
            $cost = $this->getAmountRemaining() / $this->getQtyOnHand();
        }

        return $cost;
    }

    public function getOriginalCostPerItem()
    {
        return $this->getAmount() / $this->getQty();
    }

    /**
     * Gets the current purchase used for inventory costing
     *
     * @return Unl_Inventory_Model_Purchase
     */
    public function getNextOutFromCollection()
    {
        return Mage::helper('unl_inventory')
            ->getActiveProductPurchases($this->getProductId(), 1)
            ->getFirstItem();
    }

    public function updateCost(Varien_Object $data)
    {
        if (!$this->getId()) {
            return $this;
        }

        if ($this->getNextOutFromCollection()->getId() == $this->getId()) {
            $this->setTryPublish(true);
            $this->setForcePublish(true);
        }

        $oldCostPerItem = $this->getOriginalCostPerItem();
        $oldAmount = $this->getAmount();

        $this->setAmount($data->getAmount());

        $origCostPerItem = $this->getOriginalCostPerItem();
        $this->setAmountRemaining($this->getQtyOnHand() * $origCostPerItem);

        /* @var $transaction Mage_Core_Model_Resource_Transaction */
        $transaction = Mage::getResourceModel('core/transaction');
        $audits = $this->getAuditCollection();

        if (count($audits)) {
            $this->setReSyncAudits(true);

            /* @var $audit Unl_Inventory_Model_Audit */
            foreach ($this->getAuditCollection() as $audit) {
                switch ($audit->getType()) {
                    case Unl_Inventory_Model_Audit::TYPE_PURCHASE:
                    case Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT:
                        if ((float)$this->getAmount() == 0) {
                            $audit->setType(Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT);
                        } else {
                            $audit->setType(Unl_Inventory_Model_Audit::TYPE_PURCHASE);
                        }

                        if ($data->getNote()) {
                            $msg = "\n\n" . '[UPDATED ' . Mage::helper('core')->formatDate(null,
                                Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, true) . '] - ' . $data->getNote();

                            $audit->setNote($audit->getNote() . $msg);
                        }
                        // intentional no-break
                    case Unl_Inventory_Model_Audit::TYPE_SALE:
                    case Unl_Inventory_Model_Audit::TYPE_CREDIT:
                        $multiplier = $audit->getType() == Unl_Inventory_Model_Audit::TYPE_SALE ? 1 : -1;

                        if ($audit->getQtyAffected() == $this->getQty()) {
                            $audit->setAmount($audit->getAmount() + $multiplier * ($oldAmount - $this->getAmount()));
                        } else {
                            $audit->setAmount($audit->getAmount() + $multiplier * (
                                ($oldCostPerItem * $audit->getQtyAffected()) - ($origCostPerItem * $audit->getQtyAffected())));
                        }
                        break;
                }
            }
        } else {
            $msg = Mage::helper('unl_inventory')->__(
                'A previously untracked purchase amount has been updated from %s to %s.',
                Mage::helper('core')->formatCurrency($oldAmount),
                Mage::helper('core')->formatCurrency($this->getAmount())
            );

            if ($data->getNote()) {
                $msg .= "\n\n" . $data->getNote();
            }

            $audit = Mage::getModel('unl_inventory/audit');
            $audit->setData(array(
                'product_id' => $this->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_NOTE_ONLY,
                'created_at' => Mage::getSingleton('core/date')->gmtDate(),
                'note' => $msg,
            ));
            $transaction->addObject($audit);
        }

        $transaction->addObject($this);
        $transaction->save();

        return $this;
    }
}
