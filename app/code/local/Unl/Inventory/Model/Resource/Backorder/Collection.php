<?php

class Unl_Inventory_Model_Resource_Backorder_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_inventory/backorder');
    }

    public function addProductFilter($productId)
    {
        $this->addFieldToFilter('product_id', $productId);

        return $this;
    }
}
