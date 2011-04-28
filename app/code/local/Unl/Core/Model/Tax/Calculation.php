<?php

class Unl_Core_Model_Tax_Calculation extends Mage_Tax_Model_Calculation
{
    /* Extended the logic of
     * @see Mage_Tax_Model_Calculation::getRateRequest()
     * to add the address object to the request if based on billing or shipping
     * (minor code duplication)
     */
    public function getRateRequest($shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null)
    {
        $request = parent::getRateRequest($shippingAddress, $billingAddress, $customerTaxClass, $store);

        if ($shippingAddress === false && $billingAddress === false && $customerTaxClass === false) {
            return $request;
        }

        $customer = $this->getCustomer();
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);

        if (($shippingAddress === false && $basedOn == 'shipping')
            || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId()) && $basedOn == 'billing')
                || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping')){
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
                $request->setFullAddress($billingAddress);
                break;
            case 'shipping':
                $request->setFullAddress($shippingAddress);
                break;
        }

        return $request;
    }
}
