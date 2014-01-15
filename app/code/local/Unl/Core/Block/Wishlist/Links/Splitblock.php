<?php

class Unl_Core_Block_Wishlist_Links_Splitblock extends Unl_Core_Block_Page_Template_Links_Splitblock
{
    protected $_position = 30;

    protected function _construct()
    {
        parent::_construct();

        $this->setUpperLabel($this->__('Wish'));
        $this->setLowerLabel($this->__('List'));

        $this->_url = $this->getUrl('wishlist');
    }

    protected function _toHtml()
    {
        if (!$this->helper('wishlist')->isAllow()) {
            return '';
        }

        return parent::_toHtml();
    }
}
