<?php

class Unl_Core_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    /* Extends
     * @see Mage_Tax_Model_Calculation::getRateRequest()
     * by first validating the zip code of US-NE addresses
     * (minor code duplication)
     */
    public function getRateRequest($shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null)
    {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address    = null;
        $customer   = $this->getCustomer();
        $basedOn    = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
            || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId())
                && $basedOn == 'billing')
                || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId())
                && $basedOn == 'shipping')
            ){
                if ($customer) {
                    $defBilling = $customer->getDefaultBillingAddress();
                    $defShipping = $customer->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                } else {
                    $basedOn = 'default';
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
        }

        /* @var $address Mage_Customer_Model_Address_Abstract */
        if (!empty($address) && $address->getCountryId() == 'US' && $address->getRegionCode() == 'NE') {
            Mage::getResourceModel('unl_core/tax_boundary_collection')->validateAddressZip($address, $store);
        }

        return parent::getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);
    }
}
