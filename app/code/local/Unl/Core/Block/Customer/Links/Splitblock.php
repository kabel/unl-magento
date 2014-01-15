<?php

class Unl_Core_Block_Customer_Links_Splitblock extends Unl_Core_Block_Page_Template_Links_Splitblock
{
    protected $_position = 10;

    protected function _construct()
    {
        parent::_construct();

        $helper = Mage::helper('customer');
        if ($helper->isLoggedIn()) {
            $this->setUpperLabel($this->__('Hello, %s', $helper->getCustomer()->getFirstname()));
        } else {
            $this->setUpperLabel($this->__('Hello. Log in'));
        }

        $this->setLowerLabel($this->__('Your Account'));

        $this->_url = $helper->getAccountUrl();
    }
}
