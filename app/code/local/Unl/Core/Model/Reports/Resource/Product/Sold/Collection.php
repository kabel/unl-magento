<?php

class Unl_Core_Model_Reports_Resource_Product_Sold_Collection extends Mage_Reports_Model_Resource_Product_Sold_Collection
{
    /* Overrides
     * @see Mage_Reports_Model_Resource_Product_Sold_Collection::setStoreIds()
     * by using the source_store_id field for filter
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where($this->getConnection()->prepareSqlCondition(
                'order_items.source_store_id', array('in' => (array)$storeIds))
            );
        }
        return $this;
    }
}
