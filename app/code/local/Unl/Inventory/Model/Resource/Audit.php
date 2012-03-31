<?php

class Unl_Inventory_Model_Resource_Audit extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
	{
		$this->_init('unl_inventory/audit', 'audit_id');
	}
}
