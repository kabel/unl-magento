<?php

class Unl_Inventory_Model_Resource_Audit extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
	{
		$this->_init('unl_inventory/audit', 'audit_id');
	}

	public function getProductsSelect()
	{
	    $select = $this->_getReadAdapter()->select()
	        ->from($this->getMainTable(), array('product_id'))
	        ->group('product_id');

	    return $select;
	}

	public function insertPurchaseAssociations($object)
	{
	    if (!$object->hasPurchaseAssociations()) {
	        return $this;
	    }

	    $adapter = $this->_getWriteAdapter();
	    $table = $this->getTable('unl_inventory/purchase_audit');

	    $rows = array();
	    foreach ($object->getPurchaseAssociations() as $assoc) {
	        $rows[] = array(
	            $assoc['purchase']->getId(),
	            $object->getId(),
	            $assoc['qty'],
            );
	    }

	    $adapter->insertArray($table, array('purchase_id', 'audit_id', 'qty_affected'), $rows);

        return $this;
	}
}
