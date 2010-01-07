<?php

class Unl_Core_Model_Mysql4_Tax_Boundary extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_core/tax_boundary', 'boundary_id');
    }
}