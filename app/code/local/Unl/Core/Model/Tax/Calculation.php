<?php

class Unl_Core_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    protected $_neRegion;

    /* Overrides
     * @see Mage_Tax_Model_Calculation::getRateRequest()
     * by first validating the zip code of US-NE addresses and
     * using an internal tax ZIP code logic
     */
    public function getRateRequest(
        $shippingAddress = null,
        $billingAddress = null,
        $customerTaxClass = null,
        $store = null)
    {
        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $this->getRateOriginRequest($store);
        }
        $address = new Varien_Object();
        $customer = $this->getCustomer();
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
            || ($billingAddress === false && $basedOn == 'billing')
        ) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId())
                && $basedOn == 'billing')
                || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId())
                && $basedOn == 'shipping')
            ) {
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
                $this->_doPreTaxValidation($address);
                break;
            case 'shipping':
                $address = $shippingAddress;
                $this->_doPreTaxValidation($address);
                break;
            case 'origin':
                $address = $this->getRateOriginRequest($store);
                break;
            case 'default':
                $address
                    ->setCountryId(Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY,
                        $store))
                    ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                    ->setPostcode(Mage::getStoreConfig(
                        Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE,
                        $store));
                break;
        }

        if (is_null($customerTaxClass) && $customer) {
            $customerTaxClass = $customer->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$customer) {
            $customerTaxClass = $this->getDefaultCustomerTaxClass($store);
        }

        $request = new Varien_Object();
        $request
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setPostcode($this->_getTaxPostcode($address, $store))
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }

    protected function _getNebraskaRegion()
    {
        if (null === $this->_neRegion) {
            $this->_neRegion = Mage::getModel('directory/region')->loadByCode('NE', 'US');
        }

        return $this->_neRegion;
    }

    /**
     * @param Varien_Object $address
     */
    protected function _doPreTaxValidation($address)
    {
        if ($address->getCountryId() == 'US'
            && ($address->getRegionCode() == 'NE' || $address->getRegionId() == $this->_getNebraskaRegion()->getId())
            && $address->getStreet(-1)
        ) {
            Mage::getResourceModel('unl_core/tax_boundary_collection')->validateAddressZip($address);
        }
    }

    /**
     * @param Varien_Object $address
     */
    protected function _getTaxPostcode($address, $store)
    {
        if ($address->getCountryId() == 'US'
            && ($address->getRegionCode() == 'NE' || $address->getRegionId() == $this->_getNebraskaRegion()->getId())
        ) {
            $resource = Mage::getResourceModel('unl_core/tax_boundary_collection');
            $zip = $resource->translateZip($address->getPostcode());

            // try to tranlate the default tax postcode
            if ($zip === false) {
                $defaultPostcode = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE, $store);

                if (Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, $store) == 'US'
                    && Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store)
                ) {
                    $zip = $resource->translateZip($defaultPostcode);
                }
            }

            // otherwise just use the postcode
            if ($zip === false) {
                return $defaultPostcode;
            }

            return $zip;
        }

        return $address->getPostcode();
    }
}
