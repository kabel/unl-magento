<?php

class Unl_Inventory_Model_Index extends Mage_Core_Model_Abstract
{
    protected function _construct()
	{
		$this->_init('unl_inventory/index');
	}

	public function publish()
	{
	    if ($this->_hasDataChanges) {
	        $this->save();
	    }

	    $this->_getResource()->publish($this);

	    $cost = $this->getCostPerItem();
	    $this->getProduct()
	        ->setCostFlag(true)
	        ->setCost($cost)
	        ->save();

	    return $this;
	}

	public function getProduct()
	{
	    if (!$this->hasProduct() && $this->hasProductId()) {
	        $this->setProduct(Mage::getModel('catalog/product')->load($this->getProductId()));
	    }

	    return $this->getData('product');
	}

	public function getCostPerItem()
	{
	    $cost = 0;
	    if ($this->getQtyOnHand()) {
	        $cost = $this->getAmount() / $this->getQtyOnHand();
	    }

	    return $cost;
	}
}
