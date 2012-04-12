<?php

class Unl_Ship_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function isOrderSupportAutoShip(Mage_Sales_Model_Order $order)
    {
        return ($order->getShippingCarrier() instanceof Unl_Ship_Model_Shipping_Carrier_Xmlship_Interface);
    }

    public function isUnlShipQueueEmpty()
    {
        $queue = $this->getUnlShipQueue();
        return empty($queue);
    }

    public function getUnlShipQueue($clear = false)
    {
        $session = Mage::getSingleton('adminhtml/session');
        return $session->getUnlShipQueue($clear);
    }

    public function setUnlShipQueue($queue)
    {
        $session = Mage::getSingleton('adminhtml/session');
        $session->setUnlShipQueue($queue);
    }

    public function dequeueUnlShipQueue()
    {
        $queue = $this->getUnlShipQueue();
        $id = array_shift($queue);
        $this->setUnlShipQueue($queue);
        return $id;
    }

    public function loadUnlPackages($shipment)
    {
        if (is_null($shipment->getAddlPackages())) {
            /* @var $collection Unl_Ship_Model_Resource_Shipment_Package_Collection */
            $collection = Mage::getModel('unl_ship/shipment_package')->getResourceCollection();
            $collection->selectNoData();
            $collection->addFieldToFilter('shipment_id', $shipment->getId());
            $shipment->setAddlPackages($collection->getItems());
        }

        return $this;
    }

    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     * @param Mage_Usa_Model_Shipping_Carrier_Abstract $carrier
     */
    public function validateRateRequestStreet(Mage_Shipping_Model_Rate_Request $request, $carrier)
    {
        $showMethod = $carrier->getConfigData('showmethod');
        $errorMsg = '';

        if ($request->getDestStreet()) {
            if (preg_match('/(?:P\.?\s*O\.?\s*)?Box\s/i', $request->getDestStreet())) {
                $errorMsg = $this->__('This shipping method does not support shipping to P.O. Boxes. Please change your shipping address or select a different method.');
            } else {
                //TODO: Implement Street Level Address Validation?
                //$errorMsg = $carrier->getAddressValidation($request);
            }
        }

        if ($errorMsg && $showMethod) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($carrier->getCarrierCode());
            $error->setCarrierTitle($carrier->getConfigData('title'));
            $error->setErrorMessage($errorMsg);
            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $carrier;
    }
}
