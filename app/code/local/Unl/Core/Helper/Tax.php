<?php

class Unl_Core_Helper_Tax extends Mage_Core_Helper_Abstract
{
    protected $_zipListingRegistry = array();

    public function zipRegister($zip, $result)
    {
        $this->_zipListingRegistry[$zip] = $result;
    }

    public function zipRegistry($zip)
    {
        if (isset($this->_zipListingRegistry[$zip])) {
            return $this->_zipListingRegistry[$zip];
        }

        return null;
    }
}
