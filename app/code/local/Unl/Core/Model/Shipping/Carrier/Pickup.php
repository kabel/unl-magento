<?php

class Unl_Core_Model_Shipping_Carrier_Pickup extends Mage_Shipping_Model_Carrier_Pickup
{
    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');

        $sourceStore = $this->_getSingleStoreFromItems($request->getAllItems());
        if (!$sourceStore) {
            if (is_null($sourceStore)) {
                $message = $this->getConfigData('specificerrmsg');
            } else {
                $message = Mage::helper('shipping')->__('All items must be from the same store to use this method');
            }
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('pickup');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($message);
            $result->append($error);
            return $result;
        }

        // Don't show it if the store has no address to display
        $this->setStore($sourceStore);
        $pickup = $this->getConfigData('pickupaddress');
        if (empty($pickup)) {
            return false;
        }

        $methods = $this->_getPickupLocations();
        foreach ($methods as $i => $description) {
            $method = Mage::getModel('shipping/rate_result_method');

            $method->setCarrier('pickup');
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod('store' . $i);
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice('0.00');
            $method->setCost('0.00');

            $method->setMethodDescription($description);

            $result->append($method);
        }



        return $result;
    }

    protected function _getPickupLocations()
    {
        $locations = explode("\n\n", $this->getConfigData('pickupaddress'));
        return $locations;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $locations = $this->_getPickupLocations();
        $methods = array();
        foreach ($locations as $i => $method) {
            $methods['store' . $i] = $this->getConfigData('name');
        }
        return $methods;
    }

    public function isAvailable($items)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $sourceStore = $this->_getSingleStoreFromItems($items);
        if (!$sourceStore) {
            return false;
        }

        $this->setStore($sourceStore);
        $pickup = $this->getConfigData('pickupaddress');
        if (empty($pickup)) {
            return false;
        }

        return true;
    }

    /**
     * Sets up the provided address to be the admin configured pickup address
     * used for accurate tax calculation
     *
     * @param Unl_Core_Model_Sales_Quote_Address $address
     */
    public function updateAddress($address)
    {
        $this->setStore($this->_getSingleStoreFromItems($address->getAllItems()));

        // clear any values that link this address to anything else
        $address->setSameAsBilling(0)
            ->unsCustomerAddressId()
            ->unsRegionId()
            ->unsFax()
            ->setSaveInAddressBook(false);

        $data = array(
            'company' => $this->getConfigData('company'),
            'street' => array(
                $this->getConfigData('address1'),
                $this->getConfigData('address2')
            ),
            'city' => $this->getConfigData('city'),
            'region' => $this->getConfigData('region_id'),
            'postcode' => $this->getConfigData('postcode'),
            'country_id' => $this->getConfigData('country_id'),
            'telephone' => $this->getConfigData('phone')
        );

        $address->addData($data);
        $address->implodeStreetAddress();
    }

    protected function _getSingleStoreFromItems($items)
    {
        $sourceStore = null;
        $c = count($items);
        $i = 0;
        while ($i < $c) {
            ++$i;
            if ($items[$i-1]->getProduct()->isVirtual() || $items[$i-1]->getParentItem()) {
                continue;
            } else {
                $sourceStore = ($items[$i-1] instanceof Mage_Sales_Model_Quote_Address_Item) ? $items[$i-1]->getQuoteItem()->getSourceStoreView() : $items[$i-1]->getSourceStoreView();
                break;
            }
        }

        for ($i; $i < $c; $i++) {
            if ($items[$i]->getProduct()->isVirtual() || $items[$i]->getParentItem()) {
                continue;
            }
            if ($items[$i]->getSourceStoreView() != $sourceStore) {
                return false;
            }
        }

        return $sourceStore;
    }
}
