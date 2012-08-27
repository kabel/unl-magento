<?php

class Unl_Core_Block_Page_Html_Affiliateheader extends Mage_Core_Block_Template
{
    public function getCacheKeyInfo()
    {
        return array(
            'PAGE_AFFILATEHEADER',
            Mage::app()->getStore()->getId(),
            (int)Mage::app()->getStore()->isCurrentlySecure(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
        );
    }

    public function _construct()
    {
        $this->setTemplate('page/html/affiliateheader.phtml');
        $this->setCacheLifetime(false);
    }
}
