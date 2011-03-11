<?php

class Unl_Ship_Model_Mysql4_Shipment_Package extends Mage_Core_Model_Mysql4_Abstract
{
	protected function _construct()
	{
		$this->_init('unl_ship/shipment_package', 'package_id');
	}
}