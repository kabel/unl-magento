<?php

class Unl_Core_Model_Sales_Order extends Mage_Sales_Model_Order
{
    /* Overrides
     * @see Mage_Sales_Model_Order::_checkState()
     * by adding state logic for orders with invoices that are write offs
     */
    protected function _checkState()
    {
        if (!$this->getId()) {
            return $this;
        }

        $userNotification = $this->hasCustomerNoteNotify() ? $this->getCustomerNoteNotify() : null;

        if (!$this->isCanceled()
            && !$this->canUnhold()
            && !$this->canInvoice()) {
            foreach ($this->getInvoiceCollection() as $invoice) {
                if ($invoice->getState() == Unl_Core_Model_Sales_Order_Invoice::STATE_WRITEOFF) {
                    if ($this->getState() !== self::STATE_CLOSED) {
                        $this->_setState(self::STATE_CLOSED, true, '', $userNotification);
                        return $this;
                    }
                }
            }

            if (!$this->canShip()) {
                if (0 == $this->getBaseGrandTotal() || $this->canCreditmemo()) {
                    if ($this->getState() !== self::STATE_COMPLETE) {
                        $this->_setState(self::STATE_COMPLETE, true, '', $userNotification);
                    }
                }
                /**
                 * Order can be closed just in case when we have refunded amount.
                 * In case of "0" grand total order checking ForcedCanCreditmemo flag
                 */
                elseif(floatval($this->getTotalRefunded()) || (!$this->getTotalRefunded()
                    && $this->hasForcedCanCreditmemo())
                ) {
                    if ($this->getState() !== self::STATE_CLOSED) {
                        $this->_setState(self::STATE_CLOSED, true, '', $userNotification);
                    }
                }
            }
        }

        if ($this->getState() == self::STATE_NEW && $this->getIsInProcess()) {
            $this->setState(self::STATE_PROCESSING, true, '', $userNotification);
        }
        return $this;
    }

    /* Overrides
     * @see Mage_Sales_Model_Order::_getEmails()
     * by using the source store attribute to get config emails
     */
    protected function _getEmails($configPath)
    {
        $items = $this->getAllVisibleItems();
        $storeIds = Mage::helper('unl_core')->getStoresFromItems($items);

        if (empty($storeIds)) {
            $storeIds[] = $this->getStoreId();
        }

        $allData = array();

        foreach ($storeIds as $store) {
            $data = Mage::getStoreConfig($configPath, $store);
            if (!empty($data)) {
                $allData += explode(',', $data);
            }
        }

        // BEGIN WAREHOUSE CODE
        if ($configPath == self::XML_PATH_EMAIL_COPY_TO) {
            $warehouseIds = array();
            foreach ($this->getAllItems() as $item) {
                if ($item->getWarehouse() && !in_array($item->getWarehouse(), $warehouseIds)) {
                    $warehouseIds[] = $item->getWarehouse();
                }
            }

            if (!empty($warehouseIds)) {
                $warehouses = Mage::getModel('unl_core/warehouse')->getResourceCollection()
                    ->addFieldToFilter('warehouse_id', array('in' => $warehouseIds))
                    ->load();
                foreach ($warehouses as $warehouse) {
                    $allData[] = $warehouse->getEmail();
                }
            }
        }
        // END WAREHOUSE CODE

        if (!empty($allData)) {
            return array_unique($allData);
        }

        return false;
    }

    /* Overrides
     * @see Mage_Sales_Model_Order::getTotalDue()
     * by also subtracting total_canceled
     */
    public function getTotalDue()
    {
        $total = $this->getGrandTotal()-$this->getTotalPaid()-$this->getTotalCanceled();
        $total = Mage::app()->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }

    /* Overrides
     * @see Mage_Sales_Model_Order::getBaseTotalDue()
     * by also subtracting base_total_canceled
     */
    public function getBaseTotalDue()
    {
        $total = $this->getBaseGrandTotal()-$this->getBaseTotalPaid()-$this->getBaseTotalCanceled();
        $total = Mage::app()->getStore($this->getStoreId())->roundPrice($total);
        return max($total, 0);
    }
}
