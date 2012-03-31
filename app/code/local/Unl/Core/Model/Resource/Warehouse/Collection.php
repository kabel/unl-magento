<?php

class Unl_Core_Model_Resource_Warehouse_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
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
