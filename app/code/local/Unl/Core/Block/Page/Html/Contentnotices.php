<?php

class Unl_Core_Block_Page_Html_Contentnotices extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array(
            'cache_lifetime' => false
        ));
    }

    public function getCacheKeyInfo()
    {
        return array(
            'PAGE_CONTENT_NOTICES',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
        );
    }

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
