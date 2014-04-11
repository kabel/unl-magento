<?php

class Unl_Core_Model_System_Config_Source_GoogleAnalytics_Version
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Unl_Core_Helper_GoogleAnalytics::VERSION_GA,
                'label' => Mage::helper('unl_core/googleAnalytics')->__('classic Google Analytics (ga.js)')
            ),
            array(
                'value' => Unl_Core_Helper_GoogleAnalytics::VERSION_ANALYTICS,
                'label' => Mage::helper('unl_core/googleAnalytics')->__('Universal Analytics (analytics.js)')
            ),
        );
    }
}
