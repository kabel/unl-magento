<?php

class Unl_Ship_Model_System_Config_Backend_Endicia_Changepassphrase extends Unl_Ship_Model_System_Config_Backend_Endicia_Nosave
{
    protected function _beforeSave()
    {
        parent::_beforeSave();

        $value = $this->getValue();
        if ($value == 1) {
            $oldPassPhrase = $this->getData('groups/usps/fields/endicia_old_passphrase/value');
            if (empty($oldPassPhrase)) {
                Mage::throwException(Mage::helper('unl_ship')->__('Please specify the old endicia pass phrase.'));
            }
        }

        return $this;
    }
}
