<?php

class Unl_Ship_Model_System_Config_Backend_Endicia_Nosave extends Mage_Core_Model_Config_Data
{
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $this->_dataSaveAllowed = false;
        return $this;
    }
}
