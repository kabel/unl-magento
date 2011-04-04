<?php

class Unl_Inventory_Model_Mysql4_Audit_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/audit');
	}

	public function addProductFilter($product)
	{
	    $this->addFieldToFilter('product_id', $product->getId());

	    return $this;
	}

	public function addCostPerItem()
	{
	    $this->addExpressionFieldToSelect('cost_per_item', 'IF({{amount}}, {{amount}} / {{qty}}, NULL)',
	        array('amount' => 'amount', 'qty' => 'qty'));

	    return $this;
	}

	/**
	 * Get the select statement to show audited products
	 *
	 * @return Unl_Inventory_Model_Mysql4_Audit_Collection
	 */
	public function selectProducts()
	{
	    $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS);
	    $select->columns(array('product_id'))
	        ->group('product_id');

	    return $this;
	}
}
