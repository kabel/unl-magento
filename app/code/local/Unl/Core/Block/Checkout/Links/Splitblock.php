<?php

class Unl_Core_Block_Checkout_Links_Splitblock extends Unl_Core_Block_Page_Template_Links_Splitblock
{
    protected $_position = 20;

    protected function _construct()
    {
        parent::_construct();

        $helper = Mage::helper('checkout/cart');

        $this->setIconClass('mrkp-icon-basket');
        $count = $helper->getSummaryCount();
        $this->setBadge($count);
        $this->setBadgeTitle($this->__('%s item' . ($count > 1 ? 's' : ''), $count));
        $this->setLowerLabel($this->__('Cart'));
        $this->setLiParams('class="top-link-cart"');

        $this->_url = $this->getUrl('checkout/cart');
    }
}
