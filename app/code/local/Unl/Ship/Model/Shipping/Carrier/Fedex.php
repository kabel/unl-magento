<?php

class Unl_Ship_Model_Shipping_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex
{
    /* Overrides
    * @see Mage_Shipping_Model_Carrier_Abstract::getTotalNumOfBoxes()
    * by adding extra logic for multiple items
    */
    public function getTotalNumOfBoxes($weight, $items = null)
    {
        if (empty($items)) {
            return parent::getTotalNumOfBoxes($weight);
        }

        /*
         reset num box first before retrieve again
        */
        $defaultBox = false;
        $this->_numBoxes = 0;
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }

            $boxIncr = 1;
            $quoteItem = $item->getQuoteItem() ? $item->getQuoteItem() : $item;
            if ($item->getProduct()->getShipsSeparately()) {
                if (!$quoteItem->getIsQtyDecimal() && $item->getQty() > 1) {
                    $boxIncr = $item->getQty();
                }
                $this->_numBoxes += $boxIncr;
            } elseif (!$defaultBox) {
                $this->_numBoxes += $boxIncr;
                $defaultBox = true;
            }
        }

        $weight = $this->convertWeightToLbs($weight);
        $maxPackageWeight = $this->getConfigData('max_package_weight');
        if($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $this->_numBoxes += ceil($weight/$maxPackageWeight) - 1;
        }
        $weight = $weight/$this->_numBoxes;
        return $weight;
    }

    /* Extends the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Fedex::setRequest()
     * by adding the origin region and city to the raw request
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::setRequest($request);
        $r = $this->_rawRequest;

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_REGION_ID, $this->getStore());
            if (is_numeric($origRegionCode)) {
                $origRegionCode = Mage::getModel('directory/region')->load($origRegionCode)->getCode();
            }
        }
        $r->setOrigRegionCode($origRegionCode);

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig(Mage_Shipping_Model_Config::XML_PATH_ORIGIN_CITY, $this->getStore()));
        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight(), $request->getAllItems());
        $r->setWeight($weight);

        return $this;
    }

    /* Extends the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Abstract::proccessAdditionalValidation()
     * by passing the rate request to a helper to validate the street address
     */
    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = parent::proccessAdditionalValidation($request);
        if (!$result || $result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            return $result;
        }

        $result = Mage::helper('unl_ship')->validateRateRequestStreet($request, $this);
        if (!$result || $result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            return $result;
        }

        return $this;
    }

    //TODO: Update the shipment creatation methods to save FedEx labels to unl_shipment_package


    /********************************************************************************************************************
     * New functions created for shipment functionality
     ********************************************************************************************************************/

    /**
     * Creates shipments through API based on request params submitted.
     *
     */
    protected function _createShipment() {
        $oldStore = $this->getStore();

        //get data from request
        $order = $this->_shiprequest->getOrder();
        $this->setStore($order->getStore());

        $packages = $this->_shiprequest->getPackages();

        $retval = array();
        try {
            foreach ($packages as $reqpackage) {
                //only send one package per request so that we can track the price of individual packages
                //shipment request
                $shipresponse = $this->_sendShipmentRequest($order, $reqpackage);
                $shipresult = $this->_parseShipmentResponse($order, $shipresponse);

                //store the results of the request if it was successful (there will be no exceptions thrown)
                $respackage = current($shipresult->getPackages());

                //create a package to store
                $pkg = Mage::getModel('unl_ship/shipment_package')
                    ->setOrderId($order->getId())
                    ->setCarrier($this->getCarrierCode())
                    ->setCarrierShipmentId($shipresult->getShipmentIdentificationNumber())
                    ->setWeightUnits($shipresult->getBillingWeightUnits())
                    ->setWeight($shipresult->getBillingWeight())
                    ->setTrackingNumber($respackage['tracking_number'])
                    ->setCurrencyUnits($shipresult->getCurrencyUnits())
                    ->setServiceOptionCharge($shipresult->getTotalSurchages())
                    ->setTransportationCharge($shipresult->getTotalFreightCharges())
                    ->setShippingTotal($shipresult->getTotalShippingCharges())
                    ->setLabelFormat($respackage['label_image_format'])
                    ->setLabelImage($respackage['label_image'])
                    ->setDateShipped(Mage::getSingleton('core/date')->gmtDate());

                $retval[$reqpackage->getPackageIndex()] = $pkg;
            }
        } catch (Mage_Shipping_Exception $e) {
            if (empty($retval)) {
                throw $e;
            } else {
                $retval[$reqpackage->getPackageIndex()] = $e->getMessage();
            }
        }

        $this->setStore($oldStore);

        return $retval;
    }

    /**
     * Submits a request for shipment to the Fedex API. It will use the settings for the store from which the specifed order originates.
     *
     * @param Mage_Sales_Model_Order $order
     * @param  $package The details of what is to be shipped.
     * @return The result of the request.
     */
    protected function _sendShipmentRequest($order, $package) {

        $shipaddress = $order->getShippingAddress();
        $store = $order->getStore();
        $orderid = $order->getRealOrderId();

        //get the path to the WSDL
        $wsdl = 'ShipService_v9.wsdl';
        $soapOptions = array(
            'soap_version' => SOAP_1_1
        );

        if ($this->getConfigFlag('test_mode')) {
            $wsdl = 'test' . $wsdl;
            $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;
        }

        $client = new Zend_Soap_Client($this->_getWsdlPath() . $wsdl, $soapOptions);

        //get request variables
        //payment type
        if ($this->getConfigFlag('third_party')) {
            $shipping_charges_payment = array(
                'PaymentType' => 'THIRD_PARTY',
                'Payor' => array(
                    'AccountNumber' => $this->getConfigData('third_party_account'),
                    'CountryCode' => $this->getConfigData('third_party_account_country')
                )
            );
        } else  {
            $shipping_charges_payment = array(
                'PaymentType' => 'SENDER',
                'Payor' => array(
                    'AccountNumber' => $this->getConfigData('account'),
                    'CountryCode' => $store->getConfig('shipping/origin/country_id')
                )
            );
        }

        //contents
        $contents = array();
        foreach ($package->getItems() as $itemId => $qty) {
            $item = $order->getItemById($itemId);
            $contents[] = array(
                'ItemNumber' => $itemId,
                'Description' => strip_tags($item->getName()),
                'ReceivedQuantity' => $qty,
            );
        }

        //service type
        $servicetype = explode('_', $order->getShippingMethod());
        array_shift($servicetype);
        $servicetype = implode('_', $servicetype);

        //shipper streetlines
        $shipperstreetlines = array($store->getConfig('shipping/origin/address1'));
        if ($store->getConfig('shipping/origin/address2') != '') {
            $shipperstreetlines[] = $store->getConfig('shipping/origin/address2');
        }
        if ($store->getConfig('shipping/origin/address3') != '') {
            $shipperstreetlines[] = $store->getConfig('shipping/origin/address3');
        }

        //recipient streetlines
        $recipientstreetlines = array($shipaddress->getStreet(1));
        if ($shipaddress->getStreet(2) != '') {
            $recipientstreetlines[] = $shipaddress->getStreet(2);
        }
        if ($shipaddress->getStreet(3) != '') {
            $recipientstreetlines[] = $shipaddress->getStreet(3);
        }

        $request = $this->_getAccessRequest();
        $request['TransactionDetail'] = array(
            'CustomerTransactionId' => $order->getRealOrderId()
        );
        $request['Version'] = $this->_getRequestVersionId('ship', '9');

        $request['RequestedShipment'] = array(
            'ShipTimestamp' => date('c'),
            'DropoffType' => $this->getConfigData('dropoff'), // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
            'ServiceType' => $servicetype,
            'PackagingType' => $package->getContainerCode(), // valid values FEDEX_BOK, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
            'Shipper' => array(
                'Contact' => array(
                    'CompanyName' => $order->getStore()->getWebsite()->getName(),
                    'PhoneNumber' => $store->getConfig('shipping/origin/phone')
                ),
                'Address' => array(
                    'StreetLines' => $shipperstreetlines,
                    'City' => $store->getConfig('shipping/origin/city'),
                    'StateOrProvinceCode' => Mage::getModel('directory/region')->load($store->getConfig('shipping/origin/region_id'))->getCode(),
                    'PostalCode' => $store->getConfig('shipping/origin/postcode'),
                    'CountryCode' => $store->getConfig('shipping/origin/country_id')
                )
            ),
            'Recipient' => array(
                'Contact' => array(
                    'PersonName' => $shipaddress->getName(),
                    'PhoneNumber' => $shipaddress->getTelephone()
                ),
                'Address' => array(
                    'StreetLines' => $recipientstreetlines,
                    'City' => $shipaddress->getCity(),
                    'StateOrProvinceCode' => $shipaddress->getRegionCode(),
                    'PostalCode' => $shipaddress->getPostcode(),
                    'CountryCode' => $shipaddress->getCountryId(),
                    'Residential' => $this->getConfigData('residence_delivery')
                )
            ),
            'ShippingChargesPayment' => $shipping_charges_payment,
            'LabelSpecification' => array(
                'LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
                'ImageType' => 'PNG', // valid values DPL, EPL2, PDF, ZPLII and PNG
                'LabelStockType' => 'PAPER_4X6'

                //TODO: Add return address functionality using PrintedLabelOrigin

            ),
            'RateRequestTypes' => array('ACCOUNT'), // valid values ACCOUNT and LIST
            'PackageCount' => 1,
            'PackageDetail' => 'INDIVIDUAL_PACKAGES',
            'RequestedPackageLineItems' => array(
                '0' => array(
                    'Weight' => array(
                        'Value' => sprintf("%01.1f", $package->getWeight()),
                        'Units' => $this->getWeightUnits() // valid values LB or KG
                    ),
                    'Dimensions' => array(
                        'Length' => $package->getLength(),
                        'Width' => $package->getWidth(),
                        'Height' => $package->getHeight(),
                        'Units' => $this->getDimensionUnits() // valid values IN or CM
                    ),
                    'CustomerReferences' => array(
                        '0' => array(
                            'CustomerReferenceType' => 'CUSTOMER_REFERENCE', // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
                            'Value' => $order->getRealOrderId()
                        )
                    ),
                    'ContentRecords' => $contents
                )
            )
        );

        //international
        //TODO: UPDATE THESE CAPABILITIES
        if ($store->getConfig('shipping/origin/country_id') != $shipaddress->getCountryId()) {
//            //determine item values and details
//            $itemdetails = array();
//            foreach ($package->getItems() as $itemId => $qty) {
//                $item = $order->getItemById($itemId);
//                $itemtotal += $item->getPrice();
//                $itemdetails[] = array(
//                    'NumberOfPieces' => 1,
//                    'Description' => $item->getName(),
//                    'CountryOfManufacture' => $store->getConfig('shipping/origin/country_id'),
//                    'Weight' => array(
//                        'Value' => $item->getWeight(),
//                        'Units' => $this->getWeightUnits()
//                    ),
//                    'Quantity' => $qty,
//                    'QuantityUnits' => 'EA',
//                    'UnitPrice' => array(
//                        'Amount' => sprintf('%01.2f', $item->getPrice()),
//                        'Currency' => $this->getCurrencyCode()
//                    ),
//                    'CustomsValue' => array(
//                        'Amount' => sprintf('%01.2f', ($item->getPrice() * $qty)),
//                        'Currency' => $this->getCurrencyCode()
//                    )
//                );
//            }
//
//            //The next 3 lines are a hack needed to trick the PHP5 SOAP client into not creating an optimized reference in the SOAP request
//            //Without it, the client will create an id="ref1" attribute on the ShippingChargesPayment element since it is the same as the
//            //'DutiesPayment' element. Adding these 2 dummy values tricks the SoapClient into thinking they are unique, but the dummy values are ignored
//            //in the actual request sent.
//            $shipping_charges_payment_int = $shipping_charges_payment;
//            $shipping_charges_payment_int['Dummy'] = '123';
//            $shipping_charges_payment_int['Payor']['Dummy2'] = '234';
//
//            $request['RequestedShipment']['InternationalDetail'] = array(
//                'DutiesPayment' => $shipping_charges_payment_int,
//                'DocumentContent' => 'NON_DOCUMENTS',
//                'CustomsValue' => array(
//                    'Amount' => sprintf('%01.2f', $package->getValue()),
//                    'Currency' => $this->getCurrencyCode()
//                ),
//                'Commodities' => $itemdetails
//            );
        }

        $this->_request = $request;
        try {
            $response = $client->processShipment($request);

            if ($response->HighestSeverity == 'FAILURE') {
                $response = null;
            }
        } catch (SoapFault $fault) {
            $response = null;
        }
        $this->_result = $response;

        return $this->_result;
    }

    /**
     * Parses the shipment request response.
     *
     * @param Mage_Sales_Model_Order $order The order to parse a response for.
     * @param $response The response. If empty, the object's result will be used.
     * @return Unl_Ship_Model_Shipment_Confirmation
     * @throws Unl_Ship_Exception
     */
    protected function _parseShipmentResponse($order, $response=null) {
        if (empty($response)) {
            $response = $this->_result;
        }

        //make sure there is a result to process
        if (empty($response)) {
            throw Mage::exception('Mage_Shipping', Mage::helper('unl_ship')->__('There was no API response to the shipment request'));
        }

        //check for success
        if ($response->HighestSeverity != 'ERROR') {
            //create the result and set the raw response
            $result = Mage::getModel('unl_ship/shipment_confirmation');
            if (!$result->setRawResponse($response, false)) {
                return $result;
            }

            $data = array();
            if (!$this->getConfigFlag('third_party')) {
                if (!is_array($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails)) {
                    $rating = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails;
                } else {
                    $rating = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0];
                }
                //get the currency units
                $data['currency_units'] = $rating->TotalNetCharge->Currency;
                //total charges
                $data['total_freight_charges'] = $rating->TotalNetFreight->Amount;
                $data['total_surcharges'] = $rating->TotalSurcharges->Amount;
                $data['total_shipping_charges'] = $rating->TotalNetCharge->Amount;

                //get the billing weight
                //units
                $data['billing_weight_units'] = $rating->TotalBillingWeight->Units;
                //weight
                $data['billing_weight'] = $rating->TotalBillingWeight->Value;

                //set values
                $result->addData($data);
            }

            //get the shipment id number
            $shipmentid = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
            $result->setShipmentIdentificationNumber($shipmentid);

            //get package data
            $packages = array(array(
                'tracking_number' => $shipmentid,
                'label_image_format' => 'png',
                'label_image' => base64_encode($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image),
            ));
            $result->setPackages($packages);

            //make sure all required params are present
            if (empty($shipmentid) || empty($packages)) {
                $errmsg = "Required parameter(s) not found in response: ";
                if (empty($shipmentid)) {
                    $errmsg .= 'CustomerTransactionId, ';
                }
                if (empty($packages)) {
                    $errmsg .= 'CompletedPackageDetails';
                }
                $errmsg = rtrim($errmsg, ', ');
                $result->setError($errmsg);
                return $result;
            }

            return $result;
        } else {
            $msg = array();
            $code = null;
            if (is_array($response->Notifications)) {
                foreach ($response->Notifications as $notification) {
                    if ($notification->Severity == 'FAILURE' || $notification->Severity == 'ERROR') {
                        $code = $notification->Code;
                    }
                    $msg[] = "{$notification->Severity}: ({$notification->Code}) {$notification->Message}";
                }
            } else {
                $msg[] = "{$response->Notifications->Severity}: ({$response->Notifications->Code}) {$response->Notifications->Message}";
                $code = $response->Notifications->Code;
            }

            throw Mage::exception('Mage_Shipping', implode("\n", $msg), $code);
        }
    }
}
