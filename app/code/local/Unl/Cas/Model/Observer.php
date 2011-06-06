<?php

class Unl_Cas_Model_Observer
{
    public function customerLogin($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */

        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->assignCustomerTags($customer, $uid);
        } else {
            Mage::helper('unl_cas')->revokeSpecialCustomerTags($customer);
        }
    }

    public function customerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($uid = $customer->getData('unl_cas_uid')) {
            $auth = Mage::helper('unl_cas')->getAuth();
            if ($auth->isLoggedIn()) {
                $auth->logout(Mage::getUrl());
            }
        }
    }

    public function onPaymentMethodImport($observer)
    {
        $data     = $observer->getEvent()->getInput();
        $quote    = $observer->getEvent()->getPayment()->getQuote();
        $customer = $quote->getCustomer();

        if ($customer->getId()) {
            if ($data->getMethod() == 'purchaseorder') {
                Mage::helper('unl_cas')->authorizeCostObject($customer);
            } else {
                Mage::helper('unl_cas')->revokeCostObjectAuth($customer);
            }

            $quote->setCustomer($customer);
        }
    }

    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote  = $observer->getEvent()->getQuote();

        if ($method instanceof Mage_Payment_Model_Method_Purchaseorder) {
            $customer = $quote->getCustomer();
            if (!$customer->getId()) {
                $result->isAvailable = false;
                return;
            } else {
                $result->isAvailable = Mage::helper('unl_cas')->isCustomerCostObjectAuthorized($customer);
                return;
            }
        }
    }

    public function onCheckoutSubmitAfterAll($observer)
    {
        $quote    = $observer->getEvent()->getQuote();
        $payment  = $quote->getPayment();
        $customer = $quote->getCustomer();

        if ($payment->getMethodInstance() instanceof Mage_Payment_Model_Method_Purchaseorder) {
            Mage::helper('unl_cas')->revokeCostObjectAuth($customer);
        }
    }
}
