<?php

class Unl_Inventory_Model_Mysql4_Audit extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_inventory/audit', 'audit_id');
	}
}
