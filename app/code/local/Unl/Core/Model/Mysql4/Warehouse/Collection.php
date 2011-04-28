<?php

class Unl_Core_Model_Mysql4_Warehouse_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_core/warehouse');
    }

    public function toOptionArray()
    {
        return $this->_toOptionArray('warehouse_id');
    }

    public function toOptionHash()
    {
        return $this->_toOptionHash('warehouse_id');
    }
}
