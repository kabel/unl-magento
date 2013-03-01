<?php

class Unl_Inventory_Model_Resource_Purchase extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/purchase', 'purchase_id');
	}

	public function loadQtyOnHand($productId)
	{
	    $read = $this->_getReadAdapter();

	    $purchase = $this->getOnHandSelect()
	        ->where('product_id = ?', $productId);

	    $backorder = Mage::getResourceModel('unl_inventory/backorder')->getBackorderedSelect()
            ->where('product_id = ?', $productId);

        $select = $read->select()
            ->from(array('p' => $purchase), array())
            ->joinLeft(array('b' => $backorder), 'b.product_id = p.product_id',
                array('qty_on_hand' => new Zend_Db_Expr('qty_stocked - ' . $read->getIfNullSql('qty_backordered', 0)))
            );

        return $read->fetchOne($select);
	}

	public function getOnHandSelect()
	{
	    $select = $this->_getReadAdapter()->select()
    	    ->from($this->getMainTable(), array('product_id', 'qty_stocked' => new Zend_Db_Expr('SUM(qty_on_hand)')))
    	    ->group('product_id');

	    return $select;
	}

	public function publishAvg($product)
	{
	    Mage::throwException('Not implemented');
	    //TODO: Save product cost from average calculation
	}
}
