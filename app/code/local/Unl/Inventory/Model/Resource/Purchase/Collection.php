<?php

class Unl_Inventory_Model_Resource_Purchase_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/purchase');
	}

	public function addActiveFilter()
	{
	    $this->addFieldToFilter('qty_on_hand', array('gt' => 0));

	    return $this;
	}

	public function addAuditFilter($auditId)
	{
	    $adapter = $this->getConnection();
	    $this->join(
	        array('pa' => 'unl_inventory/purchase_audit'),
	        'main_table.purchase_id = pa.purchase_id AND ' . $adapter->quoteInto('pa.audit_id = ?', $auditId),
	        array('qty_affected' => 'qty')
	    );

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
	 * Ensures the returned collection is in the order that purchases should be used
	 * (for valuation purposees).
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

	    $this->setOrder('purchase_id', self::SORT_ORDER_DESC);

	    return $this;
	}
}
