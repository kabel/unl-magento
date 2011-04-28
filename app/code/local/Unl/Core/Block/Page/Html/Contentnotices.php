<?php

class Unl_Core_Block_Page_Html_Contentnotices extends Mage_Core_Block_Template
{
    /**
     * Check if the content notice is enabled
     *
     * @return boolean
     */
    public function displayNotice()
    {
        return Mage::getStoreConfigFlag('design/contentnotice/active');
    }

    public function getNoticeType()
    {
        return Mage::getStoreConfig('design/contentnotice/type');
    }

    public function getNoticeTitle()
    {
        return Mage::getStoreConfig('design/contentnotice/title');
    }

    public function getNotice()
    {
        return Mage::getStoreConfig('design/contentnotice/message');
    }
}
