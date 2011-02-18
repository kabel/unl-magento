<?php

class Unl_Core_Model_Sales_Order extends Mage_Sales_Model_Order
{
    protected function _getEmails($configPath)
    {
        $storeIds = array();

        $items = $this->getAllVisibleItems();
        foreach ($items as $item) {
            if ($store = $item->getSourceStoreView()) {
                if (!in_array($store, $storeIds)) {
                    $storeIds[] = $store;
                }
            }
        }

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
                if ($item->getWarehouse()) {
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
}