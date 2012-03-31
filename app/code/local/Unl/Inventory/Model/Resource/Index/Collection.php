<?php

class Unl_Inventory_Model_Resource_Index_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index');
	}

	/**
	 * Sets the select statement used to show the current qty_on_hand for a product
	 *
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
	public function selectQtyOnHand()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array('qty' => 'SUM(qty_on_hand)', 'product_id'))
	        ->group('product_id');

	    return $this;
	}

	/**
	 * Changes the collection to return a valuation aggregation
	 *
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
	public function selectValuation()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array(
    	    	'qty' => 'SUM(qty_on_hand)',
	            'value' => 'SUM(amount)',
	            'avg_cost' => 'SUM(amount) / SUM(qty_on_hand)',
    	    	'product_id'
	        ))
	        ->group('product_id');

	    return $this;
	}

	/**
	 * Adds a product filter to the collection
	 *
	 * @param int $productId
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
	public function addProductFilter($productId)
	{
	    $this->addFieldToFilter('product_id', $productId);

	    return $this;
	}

	/**
	 * Enter description here ...
	 *
	 * @param unknown_type $accounting
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
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

	    $this->setOrder('index_id', self::SORT_ORDER_DESC);

	    return $this;
	}

	/**
	 * Sets the select statement to aggregate all product indexes
	 *
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
	public function selectFlatten()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array(
    	        'product_id',
    	        'qty_on_hand' => 'SUM(qty_on_hand)',
    	        'amount' => 'SUM(amount)',
    	        'created_at' => new Zend_Db_Expr("'" . Mage::getSingleton('core/date')->gmtDate() . "'")
            ))
	        ->group('product_id');

	    return $this;
	}

	/**
	 * Sets the select statement to fetch the next out product index
	 *
	 * @return Unl_Inventory_Model_Resource_Index_Collection
	 */
	public function selectRebuild()
	{
	    $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $innerSelect = clone $select;

	    $innerSelect->columns(array(
	            'product_id',
	            'next_out' => ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_LIFO ? 'MAX' : 'MIN') . '(created_at)'
	        ))
	        ->group('product_id');

	    $select->columns(array('index_id', 'product_id'))
	        ->join(array('i' => $innerSelect), 'main_table.product_id = i.product_id AND main_table.created_at = i.next_out', array())
	        ->order('index_id DESC')
	        ->limit(1);

	    return $this;
	}
}
