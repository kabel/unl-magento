<?php

class Unl_Core_Model_Sales_Order extends Mage_Sales_Model_Order
{
    protected function _getEmails($configPath)
    {
        $storeIds = array();
        
        $items = $this->getItemsCollection(false, true);
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
        
        if (!empty($allData)) {
            return array_unique($allData);
        }
        
        return false;
    }
}