<?php

class Unl_Inventory_Model_Mysql4_Index_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index');
	}


	/**
	 * Get the select statement used to show the current qty_on_hand for a product
	 *
	 * @return Varien_Db_Select
	 */
	public function getQtyOnHandSelect()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array('qty' => 'SUM(qty_on_hand)', 'product_id'))
	        ->group('product_id');

	    return $select;
	}
}
