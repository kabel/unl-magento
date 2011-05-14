<?php

class Unl_CustomerTag_Model_Mysql4_Tag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_customertag/tag');
	}

	/**
	 * Adds a collection filter on the linking table to customer
	 *
	 * @param int $customerId
	 * @return Unl_CustomerTag_Model_Mysql4_Audit_Collection
	 */
	public function addCustomerFilter($customerId)
	{
	    if (!$this->getFlag('customer')) {
    	    $this->getSelect()->join(
    	        array('cl' => $this->getTable('unl_customertag/link')),
    	        $this->getConnection()->quoteInto('main_table.tag_id = cl.tag_id AND cl.customer_id = ?', $customerId),
    	        array('created_at')
    	    );

    	    $this->setFlag('customer', true);
	    }
	    return $this;
	}

	/**
	 * Adds a collection filter on the linking table to product
	 *
	 * @param int $productId
	 * @return Unl_CustomerTag_Model_Mysql4_Audit_Collection
	 */
	public function addProductFilter($productId)
	{
	    if (!$this->getFlag('product')) {
    	    $this->getSelect()->join(
    	        array('pl' => $this->getTable('unl_customertag/product_link')),
    	        $this->getConnection()->quoteInto('main_table.tag_id = pl.tag_id AND pl.product_id = ?', $productId),
    	        array()
    	    );

    	    $this->setFlag('product', true);
	    }
	    return $this;
	}

	/**
	 * Adds a collection filter on the linking table to category
	 *
	 * @param int $categoryId
	 * @return Unl_CustomerTag_Model_Mysql4_Audit_Collection
	 */
	public function addCategoryFilter($categoryId)
	{
	    if (!$this->getFlag('category')) {
    	    $this->getSelect()->join(
    	        array('cl' => $this->getTable('unl_customertag/category_link')),
    	        $this->getConnection()->quoteInto('main_table.tag_id = cl.tag_id AND cl.category_id = ?', $categoryId),
    	        array()
    	    );

    	    $this->setFlag('category', true);
	    }
	    return $this;
	}
}
