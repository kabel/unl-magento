<?php

class Unl_AdminLog_Model_Source_Result
{
    const SUCCESS  = 1;
    const FAIL     = 2;

    public function toOptionHash()
    {
        $options = array(
            self::SUCCESS  => Mage::helper('unl_adminlog')->__('success'),
            self::FAIL => Mage::helper('unl_adminlog')->__('fail'),
        );

        return $options;
    }
}
