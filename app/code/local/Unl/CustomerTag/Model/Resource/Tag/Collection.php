<?php

class Unl_CustomerTag_Model_Resource_Tag_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_customertag/tag');
	}

	/**
	 * Adds a collection filter on a linking table
	 *
	 * @param string $type
	 * @param string|int $id
	 * @param string $alias
	 * @param array $cols
	 * @return Unl_CustomerTag_Model_Resource_Tag_Collection
	 */
	protected function _addLinkFilter($type, $id, $alias, $cols = array(), $left = false)
	{
	    $col = $flag = $type;
	    if (empty($type)) {
	        $col = $flag = 'customer';
	    } else {
            $type = rtrim($type, '_') . '_';
        }
	    $col .= '_id';

	    if (!$this->getFlag($flag)) {
	        $table = "unl_customertag/{$type}link";
	        $cond  = "main_table.tag_id = {$alias}.tag_id AND " . $this->getConnection()->prepareSqlCondition("{$alias}.{$col}", $id);

	        if ($left) {
	            $this->getSelect()->joinLeft(array($alias => $this->getTable($table)), $cond, $cols);
	        } else {
    	        $this->join(array($alias => $table), $cond, $cols);
	        }

	        $this->setFlag($flag, true);
	    }

	    return $this;
	}

	/**
	 * Adds a collection filter on the linking table to customer
	 *
	 * @param int $customerId
	 * @return Unl_CustomerTag_Model_Resource_Tag_Collection
	 */
	public function addCustomerFilter($customerId, $left = false)
	{
	    return $this->_addLinkFilter('', $customerId, 'cl', array('created_at'), $left);
	}

	/**
	 * Adds a collection filter on the linking table to product
	 *
	 * @param int $productId
	 * @return Unl_CustomerTag_Model_Resource_Tag_Collection
	 */
	public function addProductFilter($productId)
	{
	    return $this->_addLinkFilter('product', $productId, 'pl');
	}

	/**
	 * Adds a collection filter on the linking table to category
	 *
	 * @param int $categoryId
	 * @return Unl_CustomerTag_Model_Resource_Tag_Collection
	 */
	public function addCategoryFilter($categoryId)
	{
	    return $this->_addLinkFilter('category', $categoryId, 'cl');
	}
}
