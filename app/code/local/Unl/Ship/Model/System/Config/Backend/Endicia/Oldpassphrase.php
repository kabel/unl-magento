<?php

class Unl_Ship_Model_System_Config_Backend_Endicia_Oldpassphrase extends Unl_Ship_Model_System_Config_Backend_Endicia_Nosave
{
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->getOldValue() != $this->getValue()) {
            Mage::register('endicia_old_passphrase', $this->getValue(), true);
        }

        return $this;
    }
}
