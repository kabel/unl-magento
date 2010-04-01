<?php

class Unl_Core_Model_Reports_Mysql4_Product_Ordered_Collection extends Mage_Reports_Model_Mysql4_Product_Ordered_Collection
{
    public function setStoreIds($storeIds)
    {
        $storeId = array_pop($storeIds);
        $this->setStoreId($storeId);
        $this->addStoreFilter($storeId);
        if ($storeId) {
            $this->getSelect()->where('order_items.source_store_view = ?', $storeId);
        }
        return $this;
    }
}
