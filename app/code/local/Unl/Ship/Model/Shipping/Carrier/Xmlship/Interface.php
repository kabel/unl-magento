<?php

interface Unl_Ship_Model_Shipping_Carrier_Xmlship_Interface
{
	/**
     * Creates a shipment to be sent. Should initialize the shipment, retrieve tracking number, and get shipping label.
     *
     * @return array An array of Mage_Shipping_Model_Shipment_Package objects
     * @throws Mage_Shipping_Exception
     */
    public function createShipment(Unl_Ship_Model_Shipment_Request $request);

	public function getCode($type, $code='');

	public function addErrorRetry($code);

	public function isErrorRetried($code);

	public function isRequestRetryAble($code);
}
