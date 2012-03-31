<?php

class Unl_Core_Model_Core_Variable_Config extends Mage_Core_Model_Variable_Config
{
    /* Overrides
     * @see Mage_Core_Model_Variable_Config::getVariablesWysiwygActionUrl()
     * by forcing the URL module to adminhtml
     */
    public function getVariablesWysiwygActionUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_variable/wysiwygPlugin');
    }
}
