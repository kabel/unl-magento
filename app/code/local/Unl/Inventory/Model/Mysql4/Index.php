<?php

class Unl_Inventory_Model_Mysql4_Index extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index', 'index_id');
	}

	public function flattenIndexes()
	{
	    $db = $this->_getWriteAdapter();
	    $db->beginTransaction();
	    try {
    	    /* @var $indexSelect Varien_Db_Select */
	        $columns = array(
	            'product_id',
	            'qty_on_hand' => 'SUM(qty_on_hand)',
	            'amount' => 'SUM(amount)',
	            'created_at' => new Zend_Db_Expr("'" . now() . "'")
	        );
    	    $indexSelect = Mage::getResourceModel('unl_inventory/index_collection')->getSelect();
    	    $indexSelect->reset(Zend_Db_Select::COLUMNS)
    	        ->columns($columns)
    	        ->group('product_id');
    	    $db->query($indexSelect->insertFromSelect($this->getTable('unl_inventory/index_tmp')));
    	    $db->truncate($this->getTable('unl_inventory/index'));

    	    array_shift($columns);
    	    $select = new Varien_Db_Select($db);
    	    $select->from($this->getTable('unl_inventory/index_tmp'));
    	    $db->query($select->insertFromSelect($this->getTable('unl_inventory/index'), array(
    	        'product_id' => false,
    	        'qty_on_hand',
    	        'amount',
    	        'created_at'
    	    )));
    	    $db->truncate($this->getTable('unl_inventory/index_tmp'));
    	    $this->rebuildIndex();
    	    $db->commit();
	    } catch (Exception $e) {
	        $db->rollback();
	        throw $e;
	    }

	    return $this;
	}

	public function rebuildIndex()
	{
	    $db = $this->_getWriteAdapter();
	    $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();

	    /* @var $indexSelect1 Varien_Db_Select */
	    $indexSelect1 = Mage::getResourceModel('unl_inventory/index_collection')->getSelect()
	        ->reset(Zend_Db_Select::COLUMNS)
	        ->columns(array(
	            'index_id' => 'MAX(index_id)',
	            'product_id'
	        ))
	        ->group('product_id');
	    /* @var $indexSelect2 Varien_Db_Select */
	    $indexSelect2 = Mage::getResourceModel('unl_inventory/index_collection')->getSelect()
	        ->reset(Zend_Db_Select::COLUMNS)
	        ->columns('product_id')
	        ->group('product_id');

	    if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_LIFO) {
	        $indexSelect2->columns(array('next_out' => 'MAX(created_at)'));
	    } else {
	        $indexSelect2->columns(array('next_out' => 'MIN(created_at)'));
	    }

	    $indexSelect1->join(array('i' => $indexSelect2), 'main_table.product_id = i.product_id AND main_table.created_at = i.next_out', array());

	    $db->truncate($this->getTable('unl_inventory/index_idx'));
	    $db->query($indexSelect1->insertFromSelect($this->getTable('unl_inventory/index_idx')));

	    return $this;
	}

	public function publishIndex(Mage_Core_Model_Abstract $object)
	{
	    $write = $this->_getWriteAdapter();
	    $write->insertOnDuplicate($this->getTable('unl_inventory/index_idx'), array(
	        'index_id' => $object->getId(),
	        'product_id' => $object->getProductId()
	    ));

	    return $this;
	}
}
