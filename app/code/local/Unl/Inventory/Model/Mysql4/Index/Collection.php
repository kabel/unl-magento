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
	 * @return Unl_Inventory_Model_Mysql4_Index_Collection
	 */
	public function selectQtyOnHand()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array('qty' => 'SUM(qty_on_hand)', 'product_id'))
	        ->group('product_id');

	    return $this;
	}

	public function addProductFilter($productId)
	{
	    $this->addFieldToFilter('product_id', $productId);

	    return $this;
	}

	public function addAccountingOrder($accounting)
	{
	    switch ($accounting) {
	        case Unl_Inventory_Model_Config::ACCOUNTING_FIFO:
	            $this->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_ASC);
	            break;
	        case Unl_Inventory_Model_Config::ACCOUNTING_LIFO:
	            $this->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);
	            break;
	        default:
	            break;
	    }

	    return $this;
	}
}
