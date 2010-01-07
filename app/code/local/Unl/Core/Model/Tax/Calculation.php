<?php

class Unl_Core_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    public function getRateRequest($shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null)
    {
        $address = new Varien_Object();
        $session = Mage::getSingleton('customer/session');
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);
        if (($shippingAddress === false && $basedOn == 'shipping') || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId()) && $basedOn == 'billing') || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping')){
                if (!$session->isLoggedIn()) {
                    $basedOn = 'default';
                } else {
                    $defBilling = $session->getCustomer()->getDefaultBillingAddress();
                    $defShipping = $session->getCustomer()->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;

            case 'shipping':
                $address = $shippingAddress;
                break;

            case 'origin':
                $address
                    ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $store))
                    ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id', $store))
                    ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $store));
                break;

            case 'default':
                $address
                    ->setCountryId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, $store))
                    ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                    ->setPostcode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE, $store));
                break;
        }

        if (is_null($customerTaxClass) && $session->isLoggedIn()) {
            $customerTaxClass = $session->getCustomer()->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$session->isLoggedIn()) {
            $defaultCustomerGroup = Mage::getStoreConfig('customer/create_account/default_group', $store);
            $customerTaxClass = Mage::getModel('customer/group')->getTaxClassId($defaultCustomerGroup);
        }
        $request = new Varien_Object();
        $request
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setPostcode($address->getPostcode())
            ->setFullAddress($address)
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }
}