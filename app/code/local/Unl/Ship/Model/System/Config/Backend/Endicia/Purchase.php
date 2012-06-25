<?php

class Unl_Ship_Model_System_Config_Backend_Endicia_Purchase extends Unl_Ship_Model_System_Config_Backend_Endicia_Nosave
{
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $value = $this->getValue();
        if (!empty($value)) {
            Mage::register('endicia_force_purchase', true, true);
        }

        return $this;
    }
}
