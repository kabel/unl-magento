<?php

class Unl_Inventory_Model_Resource_Audit_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/audit');
	}

	/**
	 * Adds a product filter to the collection
	 *
	 * @param unknown_type $product
	 * @return Unl_Inventory_Model_Resource_Audit_Collection
	 */
	public function addProductFilter($product)
	{
	    $this->addFieldToFilter('product_id', $product->getId());

	    return $this;
	}
	
	public function addPurchaseFilter($purchaseId)
	{
	    $adapter = $this->getConnection();
	    $this->join(
	        array('pa' => 'unl_inventory/purchase_audit'), 
	        'main_table.audit_id = pa.audit_id AND ' . $adapter->quoteInto('pa.purchase_id = ?', $purchaseId), 
	        array('qty_affected' => 'qty')
        );
	}

	/**
	 * Adds a cost per item expression to the selected collection
	 *
	 * @return Unl_Inventory_Model_Resource_Audit_Collection
	 */
	public function addCostPerItem()
	{
	    $this->addExpressionFieldToSelect('cost_per_item', 'IF({{amount}}, {{amount}} / {{qty}}, NULL)',
	        array('amount' => 'amount', 'qty' => 'qty'));

	    return $this;
	}
}
