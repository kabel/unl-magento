<?php

class Unl_Inventory_Model_Index extends Mage_Core_Model_Abstract
{
    protected function _construct()
	{
		$this->_init('unl_inventory/index');
	}

	public function publishIndex()
	{
	    if ($this->_hasDataChanges) {
	        $this->save();
	    }

	    $this->_getResource()->publishIndex($this);

	    return $this;
	}
}
