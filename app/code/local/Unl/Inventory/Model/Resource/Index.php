<?php

class Unl_Inventory_Model_Resource_Index extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index', 'index_id');
	}

	/**
	 * Merges all inventory indexes into one
	 *
	 * @throws Exception
	 * @return Unl_Inventory_Model_Resource_Index
	 */
	public function flattenIndexes()
	{
	    $adapter = $this->_getWriteAdapter();

	    $adapter->beginTransaction();
	    try {
    	    $collection = Mage::getModel('unl_inventory/index')->getResourceCollection()->selectFlatten();

    	    $adapter->query($collection->getSelect()->insertFromSelect($this->getTable('unl_inventory/index_tmp')));

    	    $adapter->delete($this->getTable('unl_inventory/index'));

    	    $select = new Varien_Db_Select($adapter);
    	    $select->from($this->getTable('unl_inventory/index_tmp'));
    	    $adapter->query($select->insertFromSelect($this->getTable('unl_inventory/index')));

    	    $adapter->commit();
	    } catch (Exception $e) {
	        $adapter->rollBack();
	        throw $e;
	    }

	    $adapter->truncate($this->getTable('unl_inventory/index_tmp'));

	    $this->rebuildIndex();

	    return $this;
	}

	/**
	 * Reassembles the current inventory index for all products
	 *
	 * @return Unl_Inventory_Model_Resource_Index
	 */
	public function rebuildIndex()
	{
	    $adapter = $this->_getWriteAdapter();

	    $adapter->truncate($this->getTable('unl_inventory/index_idx'));

	    $adapter->beginTransaction();
	    try {
	        $collection = Mage::getModel('unl_inventory/index')->getResourceCollection()->selectRebuild();

    	    $adapter->query($collection->getSelect()->insertFromSelect($this->getTable('unl_inventory/index_idx')));
	    } catch (Exception $e) {
	        $adapter->rollBack();
	        throw $e;
	    }

	    return $this;
	}

	/**
	 * Forces an inventory index for a product
	 *
	 * @param Mage_Core_Model_Abstract $object
	 * @return Unl_Inventory_Model_Resource_Index
	 */
	public function publish(Mage_Core_Model_Abstract $object)
	{
	    $this->_getWriteAdapter()->insertOnDuplicate($this->getTable('unl_inventory/index_idx'), array(
	        'index_id' => $object->getId(),
	        'product_id' => $object->getProductId()
	    ));

	    return $this;
	}
}
