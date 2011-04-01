<?php

class Unl_Inventory_Model_Mysql4_Index extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/index', 'index_id');
	}
}