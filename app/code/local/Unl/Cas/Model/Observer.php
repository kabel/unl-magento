<?php

class Unl_Cas_Model_Observer
{
    public function customerLogin($observer)
    {
        //$loggedIn = Mage::getSingleton('customer/session')->isLoggedIn();
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        
        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->assignGroupId($customer, $uid);
            if ($customer->dataHasChangedFor('group_id')) {
                $customer->save();
            }
        }
    }
    
    public function customerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->getAuth()->logout(Mage::getUrl());
        }
    }
}
