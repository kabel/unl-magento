<?php

class Unl_Core_Model_Mysql4_Warehouse extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_core/warehouse', 'warehouse_id');
    }

    public function delete(Mage_Core_Model_Abstract $object)
    {
        parent::delete($object);
        $this->_getWriteAdapter()->update(
            $this->getTable('sales/quote_item'),
            array('warehouse' => null),
            $this->_getWriteAdapter()->quoteInto('warehouse = ?', $object->getId())
        );
        $this->_getWriteAdapter()->update(
            $this->getTable('sales/order_item'),
            array('warehouse' => null),
            $this->_getWriteAdapter()->quoteInto('warehouse = ?', $object->getId())
        );

        return $this;
    }
}