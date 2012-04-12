<?php

class Unl_Ship_Model_Shipping_Carrier_Ups_Source_RequestOption
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('unl_ship')->__('No address validation'),
                'value' => 'nonvalidate'
            ),
            array(
                'label' => Mage::helper('unl_ship')->__('Fail on failed address validation'),
                'value' => 'validate'
            ),
        );
    }
}
