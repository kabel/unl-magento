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

	public function mergeRemainingPurchases()
	{
	    $adapter = $this->_getWriteAdapter();

	    $select = $adapter->select()
	        ->from($this->getMainTable(), array(
    	        'product_id',
    	        'SUM(qty_on_hand)',
    	        'SUM(amount_remaining)',
            ))
            ->where('qty_on_hand > ?', 0)
	        ->group('product_id');

        $adapter->beginTransaction();
        try {
            $adapter->query($select->insertFromSelect($this->getTable('unl_inventory/index_tmp')));

            $adapter->update($this->getMainTable(), array(
                'qty_on_hand' => 0,
                'amount_remaining' => 0,
                'qty' => new Zend_Db_Expr('(qty - qty_on_hand)'),
                'amount' => new Zend_Db_Expr('(amount - amount_remaining)'),
            ), array(
                'qty_on_hand > ?' => 0
            ));

            $select = $adapter->select()
                ->from($this->getTable('unl_inventory/index_tmp'), array(
                    'product_id',
                    'qty_on_hand',
                    'amount_remaining' => 'amount',
                    'created_at' => new Zend_Db_Expr("'" . Mage::getSingleton('core/date')->gmtDate() . "'"),
                    'qty' => 'qty_on_hand',
                    'amount' => 'amount'
                ));
            $adapter->query($select->insertFromSelect($this->getMainTable(), array(
                'product_id',
                'qty_on_hand',
                'amount_remaining',
                'created_at',
                'qty',
                'amount',
            )));

            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        $adapter->truncateTable($this->getTable('unl_inventory/index_tmp'));

        return $this;
	}
}
