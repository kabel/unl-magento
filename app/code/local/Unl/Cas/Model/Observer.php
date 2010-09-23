<?php

class Unl_Cas_Model_Observer
{
    public function customerLogin($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */
        
        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->assignGroupId($customer, $uid);
        } else {
            Mage::helper('unl_cas')->revokeSpecialCustomerGroup($customer);
        }
    }
    
    public function customerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        
        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->getAuth()->logout(Mage::getUrl());
        }
    }
    
    //TODO: Add a listener to 'sales_quote_payment_import_data_before' event to change customer class to UNL Cost Object Authorized
}
