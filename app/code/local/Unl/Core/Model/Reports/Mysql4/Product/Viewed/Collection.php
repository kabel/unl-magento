<?php

class Unl_Core_Model_Reports_Mysql4_Product_Viewed_Collection extends Mage_Reports_Model_Mysql4_Product_Viewed_Collection
{
    public function setStoreIds($storeIds)
    {
        $storeId = array_pop($storeIds);
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);
        if ($storeId) {
            $this->addAttributeToFilter('source_store_view', array('eq' => $storeId));
        }
        return $this;
    }
}
