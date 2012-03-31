<?php

class Unl_Core_Block_Page_Html_Titlegraphic extends Mage_Core_Block_Template
{
    public function _construct()
    {
        $this->setTemplate('page/html/titlegraphic.phtml');
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
