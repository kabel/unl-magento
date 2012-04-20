<?php

class Unl_Core_Model_Backup_Config_Source_RsyncRetain
{
    /**
     * return possible options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('unl_core')->__('1 Week'),
                'value' => '7',
            ),
            array(
                'label' => Mage::helper('unl_core')->__('2 Weeks'),
                'value' => '14',
            ),
            array(
                'label' => Mage::helper('unl_core')->__('1 Month (30 Days)'),
                'value' => '30',
            ),
            array(
                'label' => Mage::helper('unl_core')->__('1 Quarter (90 Days)'),
                'value' => '90',
            ),
            array(
                'label' => Mage::helper('unl_core')->__('1/2 Year (180 Days)'),
                'value' => '180',
            ),
            array(
                'label' => Mage::helper('unl_core')->__('1 Year (365 Days)'),
                'value' => '365',
            ),
        );
    }
}
