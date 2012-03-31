<?php

class Unl_Core_Model_Shipping_Carrier_Pickup extends Mage_Shipping_Model_Carrier_Pickup
{
    /**
     * Returns if this method will be available for the items
     *
     * @param array $items
     * @return boolean
     */
    public function isAvailable($items)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $stores = $this->_getStoresFromItems($items);

        if (empty($stores) || !$this->_validatePickup($stores)) {
            return false;
        }

        $this->setStore(current($stores));
        $pickup = $this->getConfigData('pickupaddress');

        if (empty($pickup)) {
            return false;
        }

        return true;
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $result = Mage::getModel('shipping/rate_result');
        $stores = $this->_getStoresFromItems($request->getAllItems());

        if (empty($stores)) {
            $this->_appendError($result, $this->getConfigData('specificerrmsg'));
            return $result;
        }

        if (!$this->_validatePickup($stores)) {
            $this->_appendError($result,
                Mage::helper('unl_core')->__('Some of your selected items are not available from the same pickup location'));
            return $result;
        }

        // Don't show it if the store has no address to display
        $this->setStore(current($stores));
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

            $method->setPrice(0);
            $method->setCost(0);

            $method->setMethodDescription($description);

            $result->append($method);
        }

        return $result;
    }

    /**
     * Append an error the the rate result
     *
     * @param Mage_Shipping_Model_Rate_Result $result
     * @param string $message
     * @return Unl_Core_Model_Shipping_Carrier_Pickup
     */
    protected function _appendError($result, $message)
    {
        $error = Mage::getModel('shipping/rate_result_error');
        $error->setCarrier('pickup');
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($message);
        $result->append($error);

        return $this;
    }

    /**
     * Returns if all the pickup addresses of the stores match
     *
     * @param array $stores
     * @return boolean
     */
    protected function _validatePickup($stores)
    {
        $origStore = $this->getStore();
        $store = array_shift($stores);
        $this->setStore($store);
        $location = $this->getConfigData('pickupaddress');

        foreach ($stores as $store) {
            $this->setStore($store);
            if ($location != $this->getConfigData('pickupaddress')) {
                $this->setStore($origStore);
                return false;
            }
        }

        $this->setStore($origStore);
        return true;
    }

    /**
     * Forwards all non-virtual items to helper to get all store_ids
     *
     * @param array $items
     */
    protected function _getStoresFromItems($items)
    {
        $quoteItems = array();
        foreach ($items as $item) {
            if (!$item->getIsVirtual()) {
                $quoteItems[] = $item;
            }
        }
        return Mage::helper('unl_core')->getStoresFromItems($quoteItems);
    }

    /**
     * Get an array of locations available for pickup
     *
     * @return array:
     */
    protected function _getPickupLocations()
    {
        $locations = explode("\n\n", $this->getConfigData('pickupaddress'));
        return $locations;
    }

    /**
     * Get the location from the given shipping method code and store
     *
     * @param string $method
     * @param array $items
     * @return boolean|string
     */
    public function getLocationFromMethod($method, $items)
    {
        $arr = explode('_', $method, 2);
        if (empty($arr[1]) || substr($arr[1], 0, 5) != 'store') {
            return false;
        }

        $i = intval(substr($arr[1], 5));

        $this->setStore(current($this->_getStoresFromItems($items)));

        $locations = $this->_getPickupLocations();
        if (isset($locations[$i])) {
            return $locations[$i];
        }

        return false;
    }

    public function getAllowedMethods()
    {
        $locations = $this->_getPickupLocations();
        $methods = array();
        foreach ($locations as $i => $method) {
            $methods['store' . $i] = $this->getConfigData('name');
        }
        return $methods;
    }

    /**
     * Sets up the provided address to be the admin configured pickup address
     * used for accurate tax calculation
     *
     * @param Unl_Core_Model_Sales_Quote_Address $address
     */
    public function updateAddress($address)
    {
        $this->setStore(current($this->_getStoresFromItems($address->getAllItems())));

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
}
