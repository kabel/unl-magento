<?php

class Unl_Core_Model_Tax_Boundary extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_core/tax_boundary');
    }
    
    public function getZipFromAddress($address)
    {
        $collection = $this->getResourceCollection();
        /* @var $collection Unl_Core_Model_Mysql4_Tax_Boundary_Collection */
        $zip = $collection->getZipFromAddress($address);
        return $zip;
    }
}