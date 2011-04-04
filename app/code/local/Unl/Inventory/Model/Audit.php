<?php

class Unl_Inventory_Model_Audit extends Mage_Core_Model_Abstract
{
    const TYPE_SALE        = 1;
    const TYPE_CREDIT      = 2;
    const TYPE_PURCHASE    = 3;
    const TYPE_ADJUSTMENT  = 4;

    const TYPE_ADJUSTMENT_SET     = 1;
    const TYPE_ADJUSTMENT_OFFSET  = 2;

    protected function _construct()
	{
		$this->_init('unl_inventory/audit');
	}
}
