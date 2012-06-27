<?php

class Unl_Core_Block_Page_Html_Titlegraphic extends Mage_Core_Block_Template
{
    public function getCacheKeyInfo()
    {
        return array(
            'PAGE_TITLEGRAPHIC',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
        );
    }

    public function _construct()
    {
        $this->setTemplate('page/html/titlegraphic.phtml');
        $this->setCacheLifetime(false);
    }

    public function isDefaultStore()
    {
        return Mage::app()->getStore()->getCode() == 'default';
    }

    public function getStoreGroup()
    {
        return Mage::app()->getGroup();
    }
}
