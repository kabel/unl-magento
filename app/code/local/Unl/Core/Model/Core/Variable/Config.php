<?php

class Unl_Core_Model_Core_Variable_Config extends Mage_Core_Model_Variable_Config
{
    public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
    }
}
