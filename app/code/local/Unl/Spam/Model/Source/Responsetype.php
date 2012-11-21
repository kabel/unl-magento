<?php

class Unl_Spam_Model_Source_Responsetype
{
    public function toOptionArray()
    {
        return array(
            array(
                'label' => Mage::helper('unl_spam')->__('403. Forbidden.'),
                'value' => Unl_Spam_Model_Blacklist::RESPONSE_TYPE_403
            ),
            array(
                'label' => Mage::helper('unl_ship')->__('503. Service Unavailable.'),
                'value' => Unl_Spam_Model_Blacklist::RESPONSE_TYPE_503
            ),
        );
    }

    public function toOptionHash()
    {
        $hash = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $hash[$option['value']] = $option['label'];
        }

        return $hash;
    }
}
