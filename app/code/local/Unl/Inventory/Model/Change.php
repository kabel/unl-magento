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
        $purchases = $helper->getActiveProductPurchases($product);
        $purchaseCount = $purchases->getSize();

        if ($changeObj->getType() == Unl_Inventory_Model_Audit::TYPE_PURCHASE
            || ($changeObj->getType() == Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT && $changeObj->getQty() > 0)
        ) {
            if ($purchaseCount && $accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
                $purchase = $purchases->getFirstItem();
                $purchase
                    ->setQty($changeObj->getQty() + $purchase->getQty())
                    ->setQtyOnHand($changeObj->getQty() + $purchase->getQtyOnHand())
                    ->setAmount($changeObj->getAmount() + $purchase->getAmount())
                    ->setAmountRemaining($changeObj->getAmount() + $purchase->getAmountRemaining())
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

                if ($changeObj->getAdjType() == Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT_SET) {
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
        $purchases = Mage::helper('unl_inventory')->getActiveProductPurchases($product);

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

                if ($qty == 0) {
                    break;
                }
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

        if ($qty < 0) {
            if (!$isSale) {
                Mage::throwException('This inventory change will result in a backorder, which is not allowed.');
            }

            if (!$actualCost) {
                $actualCost = $audit->getAmount() * -1;
                $actualCost += $audit->getCostPerItem() * $qty;
            }

            $backorder = Mage::getModel('unl_inventory/backorder');
            $backorder->setData(array(
                'product_id' => $product->getId(),
                'qty' => $qty * -1,
                'parent_id' => $audit->getInvoiceItemId(),
            ));
            $backorder->save();
        }

        if ($isSale) {
            if ($actualCost && ($actualCost * -1) != $audit->getAmount()) {
                $audit->setAmount($actualCost * -1);
                $audit->syncItemCost();
            }

            $audit->setPurchaseAssociations($auditPurchases);
        }

        if ($qty >= 0 && $republish) {
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
        $purchases = Mage::helper('unl_inventory')->getActiveProductPurchases($product);
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

    public function handleConfigChange($oldManageStock, $oldAccounting)
    {
        $configManageStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        if ($oldManageStock != $configManageStock && !$configManageStock) {
            // batch 1000 products
            $products = Mage::getResourceModel('unl_inventory/products_collection');
            $products
                ->addAttributeToFilter('audit_inventory', true)
                ->addManageStockFilter()
                ->setPage(1, 1000)
                ->setFlag('require_stock_items', true);

            for ($i = 1; $i <= $products->getLastPageNumber(); $i++) {
                foreach ($products as $product) {
                    $product->setAuditInventory(false)->save();
                }

                $products->clear();
                $products->setCurPage($i + 1);
            }
        }

        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        if ($oldAccounting != $accounting) {
            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
                Mage::getResourceSingleton('unl_inventory/purchase')->mergeRemainingPurchases();
            }

            $helper = Mage::helper('unl_inventory');
            $products = Mage::getResourceModel('unl_inventory/products_collection');
            $products
                ->addAttributeToFilter('audit_inventory', true)
                ->joinNewCost()
                ->setPage(1, 1000)
                ->setFlag('require_stock_items', true);

            for ($i = 1; $i <= $products->getLastPageNumber(); $i++) {
                foreach ($products as $product) {
                    $product
                        ->setCostFlag(true)
                        ->setCost($product->getNewCost())
                        ->save();
                }

                $products->clear();
                $products->setCurPage($i + 1);
            }
        }
    }
}
