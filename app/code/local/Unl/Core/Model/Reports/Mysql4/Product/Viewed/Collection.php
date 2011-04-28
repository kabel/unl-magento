<?php

class Unl_Core_Model_Reports_Mysql4_Product_Viewed_Collection extends Mage_Reports_Model_Mysql4_Product_Viewed_Collection
{
    /* Extends the logic of
     * @see Mage_Reports_Model_Mysql4_Product_Viewed_Collection::setStoreIds()
     * by adding a source_store filter
     */
    public function setStoreIds($storeIds)
    {
        parent::setStoreIds($storeIds);
        if ($this->getStoreId()) {
            $this->addAttributeToFilter('source_store_view', array('eq' => $this->getStoreId()));
        }
        return $this;
    }
}
