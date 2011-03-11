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
}