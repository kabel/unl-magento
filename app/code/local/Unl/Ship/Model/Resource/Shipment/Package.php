<?php

class Unl_Ship_Model_Resource_Shipment_Package extends Mage_Core_Model_Resource_Db_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_ship/shipment_package', 'package_id');
	}
}
