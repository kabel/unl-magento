<?php

class Unl_Inventory_Model_Mysql4_Index_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index');
	}
}
