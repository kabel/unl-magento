<?php

class Unl_Inventory_Model_Resource_Backorder extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_inventory/backorder', 'backorder_id');
    }
    
    public function getBackorderedSelect()
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('product_id', 'qty_backordered' => new Zend_Db_Expr('SUM(qty)')))
            ->group('product_id');
         
        return $select;
    }
}
