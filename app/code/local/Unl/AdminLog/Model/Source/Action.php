<?php

class Unl_AdminLog_Model_Source_Action
{
    const LOGIN   = 1;
    const VIEW    = 2;
    const SAVE    = 3;
    const DELETE  = 4;
    const OTHER   = 5;
    const UTILITY = 6;

    public function toOptionHash()
    {
        $options = array(
            self::LOGIN   => Mage::helper('unl_adminlog')->__('login'),
            self::DELETE  => Mage::helper('unl_adminlog')->__('delete'),
            self::SAVE    => Mage::helper('unl_adminlog')->__('save'),
            self::VIEW    => Mage::helper('unl_adminlog')->__('view'),
            self::UTILITY => Mage::helper('unl_adminlog')->__('utility'),
            self::OTHER   => Mage::helper('unl_adminlog')->__('other'),
        );

        return $options;
    }
}