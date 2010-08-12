<?php

class Unl_Core_Model_System_Config_Source_Storearticle
{

    public function toOptionArray()
    {
        return array(
            array('value' => 'the', 'label' => Mage::helper('adminhtml')->__('the')),
            array('value' => 'at', 'label' => Mage::helper('adminhtml')->__('at')),
        );
    }

}