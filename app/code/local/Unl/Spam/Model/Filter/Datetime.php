<?php

class Unl_Spam_Model_Filter_Datetime implements Zend_Filter_Interface
{
    public function filter($value)
    {
        $format = Mage::app()->getLocale()->getDateTimeFormat(
            Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM
        );

        return Mage::app()->getLocale()
            ->date($value, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString($format);
    }
}
