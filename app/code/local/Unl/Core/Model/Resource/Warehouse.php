<?php

class Unl_Core_Model_Resource_Warehouse extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_core/warehouse', 'warehouse_id');
    }

    public function _afterDelete(Mage_Core_Model_Abstract $object)
    {
        $adapter = $this->_getWriteAdapter();

        $adapter->update($this->getTable('sales/quote_item'), array('warehouse' => null),
            $adapter->prepareSqlCondition('warehouse', $object->getId())
        );
        $adapter->update($this->getTable('sales/order_item'), array('warehouse' => null),
            $adapter->prepareSqlCondition('warehouse', $object->getId())
        );

        return parent::_afterDelete($object);
    }
}
