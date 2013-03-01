<?php

class Unl_Inventory_Model_Change
{
    /**
     * Handles object validation/creation from inventory change controller post
     *
     * @param Mage_Catalog_Model_Product $product
     * @param array $data POST'd data
     */
    public function handlePost($product, $data)
    {
        $helper = Mage::helper('unl_inventory');
        $qty = $helper->getQtyOnHand($product->getId());
        $changeObj = new Varien_Object($data);

        $this->_validate($changeObj, $qty);

        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        $purchases = $this->_getPurchaseCollection($product);
        $purchaseCount = $purchases->getSize();

        if ($changeObj->getType() == Unl_Inventory_Model_Audit::TYPE_PURCHASE
            || ($changeObj->getType() == Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT && $changeObj->getQty() > 0)
        ) {
            if ($purchaseCount && $accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
                $purchase = $purchases->getFirstItem();
                $purchase
                    ->setQtyOnHand($changeObj->getQty() + $purchase->getQtyOnHand())
                    ->setAmount($changeObj->getAmount() + $purchase->getAmountRemaining())
                    ->setProduct($product)
                    ->setTryPublish(true);
            } else {
                $purchase = Mage::getModel('unl_inventory/purchase');
                $purchase->setData(array(
                    'product' => $product,
                    'product_id' => $product->getId(),
                    'qty' => $changeObj->getQty(),
                    'amount' => $changeObj->getType() == Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT ? 0 : $changeObj->getAmount(),
                    'note' => $changeObj->getNote(),
                    'force_publish' => ($purchaseCount == 0),
                ));
            }

            $purchase->save();

            $this->_adjustStockItem($product, $changeObj->getQty());
        } elseif ($purchaseCount) {
            $audit = Mage::getModel('unl_inventory/audit');
            $audit->setData(array(
                'product' => $product,
                'product_id' => $product->getId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT,
                'qty' => $changeObj->getQty(),
                'amount' => 0,
                'note' => $changeObj->getNote(),
            ));

            $this->adjustInventory($audit);

            $this->_adjustStockItem($product, $changeObj->getQty());
        }
    }

    /**
     * Checks user-input $changeObj to ensure it follows business rules
     * for purchasing or adjusting inventory.
     *
     * 1) Purchases must have a positive qty and amount
     * 2) Adjustments must have a qty and not cause inventory to backorder
     *
     * @param Varien_Object $changeObj
     * @param float $qty Quantity on hand
     */
    protected function _validate($changeObj, $qty)
    {
        $helper = Mage::helper('unl_inventory');

        switch ($changeObj->getType()) {
            case Unl_Inventory_Model_Audit::TYPE_PURCHASE:
                if ($changeObj->getQty() <= 0) {
                    Mage::throwException($helper->__('A valid, positive Qty is required.'));
                }

                if ($changeObj->getAmount() <= 0) {
                    Mage::throwException($helper->__('A purchase amount must be greater than 0.'));
                }

                break;

            case Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT:
                $changeObj->unsAmount();

                if ($changeObj->getAdjType() == self::TYPE_ADJUSTMENT_SET) {
                    if ($changeObj->getQty() < 0) {
                        Mage::throwException($helper->__('A valid, positive Qty is required.'));
                    }
                    if ($qty == $changeObj->getQty()) {
                        Mage::throwException($helper->__('The Qty on hand is already at the provided value.'));
                    }
                    $changeObj->setQty($changeObj->getQty() - $qty);
                } else {
                    if ($changeObj->getQty() == 0) {
                        Mage::throwException($helper->__('A valid Qty is required.'));
                    }
                    if (($changeObj->getQty() + $qty) < 0) {
                        Mage::throwException($helper->__('The Qty offset cannot result in a negative on hand qty.'));
                    }
                }

                break;

            default:
                Mage::throwException($helper->__('Invalid update type.'));
        }
    }

    /**
     * Get a sorted collection of active purchases for a product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Unl_Inventory_Model_Resource_Purchase_Collection
     */
    protected function _getPurchaseCollection($product)
    {
        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        $purchases = Mage::getResourceModel('unl_inventory/purchase_collection')
            ->addActiveFilter()
            ->addProductFilter($product->getId())
            ->addAccountingOrder($accounting);

        return $purchases;
    }

    /**
     * Adjusts the internal Magento inventory object by given qty
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $qty
     */
    protected function _adjustStockItem($product, $qty)
    {
        $item = $product->getStockItem();
        $item->addQty($qty);

        $verify = $item->verifyStock();
        if ($item->getIsInStock() != $verify) {
            $item->setIsInStock($verify)
            ->setStockStatusChangeAutomaticallyFlag(true);
        }

        $item->save();
    }

    /**
     * Updates the Qty on hand calculation entities based on a registered audit
     *
     * @param Unl_Inventory_Model_Audit $audit
     */
    public function adjustInventory($audit)
    {
        if ($audit->getType() == Unl_Inventory_Model_Audit::TYPE_CREDIT) {
            return $this->handleCredit($audit);
        }

        //$qty should never be positive
        $qty = $audit->getQty();
        $product = $audit->getProduct();

        $isSale = $audit->getType() == Unl_Inventory_Model_Audit::TYPE_SALE;
        $republish  = false;

        // link remaining change to purchases
        $auditPurchases = array();
        $actualCost = 0;
        $purchases = $this->_getPurchaseCollection($product);

        foreach ($purchases as $purchase) {
            $step = $qty;
            $qty += $purchase->getQtyOnHand();

            if ($qty <= 0) {
                $republish = true;

                if ($isSale) {
                    $auditPurchases[] = array(
                        'purchase' => $purchase,
                        'qty' => $purchase->getQtyOnHand(),
                    );

                    $actualCost += $purchase->getAmountRemaining();
                }

                $purchase->setQtyOnHand(0);
                $purchase->setAmountRemaining(0);
                $purchase->save();
            } else {
                $tempCost = $purchase->getCostPerItem() * $step * -1;

                if ($isSale) {
                    $auditPurchases[] = array(
                        'purchase' => $purchase,
                        'qty' => $step * -1,
                    );

                    if ($actualCost) {
                        $actualCost += $tempCost;
                    }
                }

                $purchase->setQtyOnHand($qty);
                $purchase->setAmountRemaining($purchase->getAmountRemaining() - $tempCost);
                $purchase->save();
                unset($tempCost);
                break;
            }
        }

        if ($isSale) {
            if ($actualCost && $actualCost != $audit->getAmount()) {
                $audit->setAmount($actualCost * -1);
                $audit->syncItemCost();
            }

            $audit->setPurchaseAssociations($auditPurchases);
        }

        if ($qty < 0) {
            if (!$isSale) {
                Mage::throwException('This inventory change will result in a backorder, which is not allowed.');
            }

            $backorder = Mage::getModel('unl_inventory/backorder');
            $backorder->setData(array(
                'product_id' => $product->getId(),
                'qty' => $qty,
                'parent_id' => $audit->getInvoiceItemId(),
            ));
            $backorder->save();
        } elseif ($republish) {
            foreach ($purchases as $purchase) {
                if (!$purchase->canPublish()) {
                    continue;
                }

                $purchase->setTryPublish(true)->save();
                break;
            }
        }

        $audit->save();
    }

    /**
     * Converts a credit audit into a purchase that will be sold next
     *
     * @param Unl_Inventory_Model_Audit $audit
     */
    public function handleCredit($audit)
    {
        $product = $audit->getProduct();
        $purchases = $this->_getPurchaseCollection($product);
        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();

        $purchase = Mage::getModel('unl_inventory/purchase');
        $purchase->setData(array(
            'product' => $product,
            'product_id' => $product->getId(),
            'qty' => $audit->getQty(),
            'amount' => $audit->getAmount(),
            'force_publish' => true,
            'stop_auto_audit' => true,
        ));
        $purchase->addAudit($audit);

        if ($purchases->getSize() == 0) {
            $purchase->setCreatedAt($audit->getCreatedAt());
        } elseif ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
            $purchase = $purchases->getFirstItem();
            $purchase
                ->setQtyOnHand($purchase->getQtyOnHand() + $audit->getQty())
                ->setAmountRemaining($purchase->getAmountRemaining() + $audit->getAmount())
                ->setTryPublish(true);
        } else {
            $dateModel = Mage::getSingleton('core/date');

            $nextOutDate = $dateModel->timestamp($purchases->getFirstItem()->getCreatedAt());
            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_LIFO) {
                $nextOutDate++;
            } else {
                $nextOutDate--;
            }

            $purchase->setCreatedAt($dateModel->gmtDate(null, $nextOutDate));
        }

        $purchase->save();
        $audit->save();
    }
}
