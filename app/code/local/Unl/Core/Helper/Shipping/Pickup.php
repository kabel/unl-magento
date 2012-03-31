<?php

class Unl_Core_Helper_Shipping_Pickup extends Mage_Core_Helper_Abstract
{
    public function isMethodPickup($method)
    {
        return strpos($method, 'pickup_') === 0;
    }

    public function getPickupLocation($method, $items)
    {
        if (!$this->isMethodPickup($method)) {
            return false;
        }

        $carrier = Mage::getModel('unl_core/shipping_carrier_pickup');
        return $carrier->getLocationFromMethod($method, $items);
    }
}
