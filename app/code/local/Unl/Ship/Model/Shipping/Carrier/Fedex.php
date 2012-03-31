<?php

class Unl_Ship_Model_Shipping_Carrier_Fedex
    extends Mage_Usa_Model_Shipping_Carrier_Fedex
    implements Unl_Ship_Model_Shipping_Carrier_Xmlship_Interface
{
    protected $_shiprequest = null;

    protected $_errorCodes = array();

    public function isErrorRetried($code)
    {
        return in_array($code, $this->_errorCodes);
    }

    public function isRequestRetryAble($code)
    {
        return false;
    }

    public function addErrorRetry($code)
    {
        $this->_errorCodes[] = $code;
    }

    /**
     * Retrieves the dimension units for this carrier
     *
     * @return string IN or CM
     */
    public function getDimensionUnits()
    {
        return $this->getConfigData('dimension_units');
    }

    /**
     * Retrieves the weight units for this carrier and store
     *
     * @return string LBS or KGS
     */
    public function getWeightUnits()
    {
        return $this->getConfigData('unit_of_measure');
    }

    protected function _getWsdlPath()
    {
        return dirname(__FILE__) . '/Fedex/wsdl/';
    }

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

    public function getCode($type, $code='')
    {
        //get the customer defined package dimensions in in and cm (rounded to 2 dec places)
        if ($this->getDimensionUnits() == 'IN') {
            $conv = array(
                'in' => 1,
                'cm' => 2.54
            );
        } else {
            $conv = array(
                'in' => (1 / 2.54),
                'cm' => 1
            );
        }
        $cdef_in = array(
            'height' => round($this->getConfigData('default_height') * $conv['in'], 2),
            'width' => round($this->getConfigData('default_width') * $conv['in'], 2),
            'length' => round($this->getConfigData('default_length') * $conv['in'], 2)
        );
        $cdef_cm = array(
            'height' => round($this->getConfigData('default_height') * $conv['cm'], 2),
            'width' => round($this->getConfigData('default_width') * $conv['cm'], 2),
            'length' => round($this->getConfigData('default_length') * $conv['cm'], 2)
        );

        $codes = array(
            'method'=>array(
                'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => Mage::helper('usa')->__('Europe First Priority'),
                'FEDEX_1DAY_FREIGHT'                  => Mage::helper('usa')->__('1 Day Freight'),
                'FEDEX_2DAY_FREIGHT'                  => Mage::helper('usa')->__('2 Day Freight'),
                'FEDEX_3DAY_FREIGHT'                  => Mage::helper('usa')->__('3 Day Freight'),
                'FEDEX_2_DAY'                         => Mage::helper('usa')->__('2 Day'),
                'FEDEX_EXPRESS_SAVER'                 => Mage::helper('usa')->__('Express Saver'),
                'FEDEX_FREIGHT'                       => Mage::helper('usa')->__('Freight'),
                'FEDEX_GROUND'                        => Mage::helper('usa')->__('Ground'),
                'FEDEX_NATIONAL_FREIGHT'              => Mage::helper('usa')->__('National Freight'),
                'FIRST_OVERNIGHT'                     => Mage::helper('usa')->__('First Overnight'),
                'GROUND_HOME_DELIVERY'                => Mage::helper('usa')->__('Home Delivery'),
                'INTERNATIONAL_ECONOMY'               => Mage::helper('usa')->__('International Economy'),
                'INTERNATIONAL_ECONOMY_FREIGHT'       => Mage::helper('usa')->__('Intl Economy Freight'),
                'INTERNATIONAL_FIRST'                 => Mage::helper('usa')->__('International First'),
                'INTERNATIONAL_PRIORITY'              => Mage::helper('usa')->__('International Priority'),
                'INTERNATIONAL_PRIORITY_FREIGHT'      => Mage::helper('usa')->__('Intl Priority Freight'),
                'PRIORITY_OVERNIGHT'                  => Mage::helper('usa')->__('Priority Overnight'),
                'SMART_POST'                          => Mage::helper('usa')->__('Smart Post'),
                'STANDARD_OVERNIGHT'                  => Mage::helper('usa')->__('Standard Overnight'),
            ),

            'dropoff'=>array(
                'REGULAR_PICKUP'         => Mage::helper('usa')->__('Regular Pickup'),
                'REQUEST_COURIER'        => Mage::helper('usa')->__('Request Courier'),
                'DROP_BOX'               => Mage::helper('usa')->__('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => Mage::helper('usa')->__('Business Service Center'),
                'STATION'               => Mage::helper('usa')->__('Station'),
            ),

            'packaging'=>array(
                'YOUR_PACKAGING' => Mage::helper('usa')->__('Your Packaging'),
                'FEDEX_ENVELOPE' => Mage::helper('usa')->__('FedEx Envelope'),
                'FEDEX_PAK'      => Mage::helper('usa')->__('FedEx Pak'),
                'FEDEX_BOX'      => Mage::helper('usa')->__('FedEx Box'),
                'FEDEX_TUBE'     => Mage::helper('usa')->__('FedEx Tube'),
                'FEDEX_10KG_BOX'  => Mage::helper('usa')->__('FedEx 10kg Box'),
                'FEDEX_25KG_BOX'  => Mage::helper('usa')->__('FedEx 25kg Box'),
            ),

            'unit_of_dimension'=>array(
                'IN' => Mage::helper('usa')->__('Inches'),
                'CM' => Mage::helper('usa')->__('Centimeters'),
            ),

            'unit_of_measure' => array(
                'LB' => Mage::helper('usa')->__('Pounds'),
                'KG' => Mage::helper('usa')->__('Kilograms')
            ),

            //these are dimensions in centimeters for each package type
            'package_dimensions_cm' => array(
                'YOUR_PACKAGING' => $cdef_cm,
                'FEDEX_ENVELOPE' => array(
                    'height' => 0,
                    'width' => 24.13,
                    'length' => 31.75,
                ),
                'FEDEX_PAK' => array(  //small
                    'height' => 0,
                    'width' => 26.04,
                    'length' => 32.39,
                ),
                'FEDEX_BOX' => array(  //medium
                    'height' => 6.03,
                    'width' => 29.21,
                    'length' => 33.66,
                ),
                'FEDEX_TUBE' => array(
                    'height' => 15.24,
                    'width' => 15.24,
                    'length' => 96.52,
                ),
                'FEDEX_10KG_BOX' => array(
                    'height' => 25.88,
                    'width' => 32.86,
                    'length' => 40.16,
                ),
                'FEDEX_25KG_BOX' => array(
                    'height' => 33.5,
                    'width' => 42.07,
                    'length' => 54.77,
                ),
            ),

            //these are dimensions in inches for each package type
            'package_dimensions_in' => array(
                'YOUR_PACKAGING' => $cdef_in,
                'FEDEX_ENVELOPE' => array(
                    'height' => 0,
                    'width' => 9.5,
                    'length' => 12.5,
                ),
                'FEDEX_PAK' => array(  //small
                    'height' => 0,
                    'width' => 10.25,
                    'length' => 12.75,
                ),
                'FEDEX_BOX' => array(  //medium
                    'height' => 2.38,
                    'width' => 11.5,
                    'length' => 13.25,
                ),
                'FEDEX_TUBE' => array(
                    'height' => 6,
                    'width' => 6,
                    'length' => 38,
                ),
                'FEDEX_10K_GBOX' => array(
                    'height' => 15.81,
                    'width' => 12.94,
                    'length' => 40.16,
                ),
                 'FEDEX_25KG_BOX' => array(
                    'height' => 13.19,
                    'width' => 16.56,
                    'length' => 21.56,
                ),
            ),
        );

        if (!isset($codes[$type])) {
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            return false;
        } else {
            return $codes[$type][$code];
        }
    }

    protected function _getAccessRequest($r = null)
    {
        if ($r === null) {
            $r = new Varien_Object(array(
                'account' => $this->getConfigData('account')
            ));
        }

        $request = array(
            'WebAuthenticationDetail' => array(
                'UserCredential' => array(
                    'Key'      => $this->getConfigData('key'),
                    'Password' => $this->getConfigData('password')
                )
            ),
            'ClientDetail' => array(
                'AccountNumber' => $r->getAccount(),
                'MeterNumber'   => $this->getConfigData('meter')
            )
        );

        return $request;
    }

    protected function _getRequestVersionId($service, $version)
    {
        if (is_string($version)) {
            $version = array($version);
        }
        $version += array('0','0','0');

        $versionId = array(
            'ServiceId' => $service,
            'Major' => $version[0],
            'Intermediate' => $version[1],
            'Minor' => $version[2]
        );

        return $versionId;
    }

    /* Overrides the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Fedex::_getXMLTracking()
     * by using the FedEx SOAP APIs
     */
    protected function _getXMLTracking($tracking, $uid = null)
    {
        $r = $this->_rawTrackingRequest;
        $wsdl = 'TrackService_v4.wsdl';
        $soapOptions = array(
            'soap_version' => SOAP_1_1
        );

        if ($this->getConfigFlag('test_mode')) {
            $wsdl = 'test' . $wsdl;
            $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;
        }

        $client = new Zend_Soap_Client($this->_getWsdlPath() . $wsdl, $soapOptions);
        $request = $this->_getAccessRequest($r);

        $request['TransactionDetail'] = array(
            'CustomerTransactionId' => '*** Track Request v4 using PHP ***'
        );

        $request['Version'] = $this->_getRequestVersionId('trck', array('4','1'));

        $request['PackageIdentifier'] = array(
            'Value' => $tracking,
            'Type' => 'TRACKING_NUMBER_OR_DOORTAG'
        );

        if ($uid !== null) {
            $request['TrackingNumberUniqueIdentifier'] = $uid;
        }

        $request['IncludeDetailedScans'] = true;

        try {
            $response = $client->track($request);

            if ($response->HighestSeverity == 'FAILURE') {
                $response = null;
            }
        } catch (SoapFault $fault) {
            $response = null;
        }

        $this->_parseXmlTrackingResponse($tracking, $response);
    }

    /* Overrides the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Fedex::_parseXmlTrackingResponse()
     * by using the FedEx SOAP APIs
     */
    protected function _parseXmlTrackingResponse($trackingvalue,$response)
    {
        $resultArr=array();

        if (empty($response)) {
            $errorTitle = Mage::helper('usa')->__('Unable to retrieve tracking');
        } elseif ($response->HighestSeverity == 'ERROR') {
            $errorTitle = array();
            if(is_array($response->Notifications))  {
                foreach ($response->Notifications as $notification)  {
                    $errorTitle[] = $notification->Severity .': '. $notification->Message;
                }
            } else {
                $errorTitle[] = $response->Notifications->Severity.': '.$response->Notifications->Message;
            }
            $errorTitle = implode("\n", $errorTitle);
        } else {
            $trackDetails = $response->TrackDetails;
            if (is_array($trackDetails)) {
                $this->_getXMLTracking($trackingvalue, $trackDetails[count($trackDetails)-1]->TrackingNumberUniqueIdentifier);
                return;
            }
            $resultArr = array(
                'status' => $trackDetails->StatusDescription,
                'service' => $trackDetails->ServiceInfo,
                'deliverydate' => $trackDetails->ActualDeliveryTimestamp,
                'deliverytime' => $trackDetails->ActualDeliveryTimestamp,
                'delivery_location' => $trackDetails->DeliveryLocationDescription,
                'signedby' => $trackDetails->DeliverySignatureName,
                'shipped_date' => $trackDetails->ShipTimestamp,
                'progressdetail' => array()
            );
            if ($trackDetails->EstimatedDeliveryTimestamp) {
                $resultArr['estimateddate'] = $trackDetails->EstimatedDeliveryTimestamp;
            }
            if ($trackDetails->ShipmentWeight->Value) {
                $resultArr['weight'] = $trackDetails->ShipmentWeight->Value . ' ' . $trackDetails->ShipmentWeight->Units;
            }

            $events = $trackDetails->Events;
            if (!is_array($events)) {
                $events = array($events);
            }
            foreach ($events as $evt) {
                    $datetime = explode('T', $evt->Timestamp);
                    $location = array();
                    foreach (array($evt->Address->City, $evt->Address->StateOrProvinceCode, $evt->Address->CountryCode) as $loc) {
                        if ($loc) {
                            $location[] = $loc;
                        }
                    }
                    $resultArr['progressdetail'][] = array(
                        'activity' => $evt->EventDescription,
                        'deliverydate' => $datetime[0],
                        'deliverytime' => $datetime[1],
                        'deliverylocation' => implode(', ', $location)
                    );
                }
        }

        if(!$this->_result){
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        if (!empty($resultArr)) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('fedex');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle ? $errorTitle : Mage::helper('usa')->__('Unable to retrieve tracking'));
            $this->_result->append($error);
         }
    }

	/* Overrides the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Fedex::_getXmlQuotes()
     * by using the FedEx SOAP APIs
     */
    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;
        $wsdl = 'RateService_v9.wsdl';
        $soapOptions = array(
            'soap_version' => SOAP_1_1
        );

        if ($this->getConfigFlag('test_mode')) {
            $wsdl = 'test' . $wsdl;
            $soapOptions['cache_wsdl'] = WSDL_CACHE_NONE;
        }

        $client = new Zend_Soap_Client($this->_getWsdlPath() . $wsdl, $soapOptions);
        $request = $this->_getAccessRequest($r);

        $request['TransactionDetail'] = array(
            'CustomerTransactionId' => '*** Rate Request v9 using PHP ***'
        );

        $request['Version'] = $this->_getRequestVersionId('crs', '9');

        $request['RequestedShipment'] = array(
            'ShipTimestamp' => date('c'),
            'DropoffType'   => $r->getDropoffType(),
            'PackagingType' => $r->getPackaging()
        );

        if ($this->getConfigFlag('third_party')) {
            $shipping_charges_payment = array(
            	'PaymentType' => 'THIRD_PARTY',
            	'Payor' => array(
            		'AccountNumber' => $this->getConfigData('third_party_account'),
            		'CountryCode' => $this->getConfigData('third_party_account_country')
                )
            );
        } else {
            $shipping_charges_payment = array(
            	'PaymentType' => 'SENDER',
            	'Payor' => array(
            		'AccountNumber' => $r->getAccount(),
            		'CountryCode' => $r->getOrigCountry()
                )
            );
        }

        $shipperStreetLines = array(Mage::getStoreConfig('shipping/origin/address1', $this->getStore()));
        if ($temp = Mage::getStoreConfig('shipping/origin/address2', $this->getStore())) {
            $shipperStreetLines[] = $temp;
        }
        if ($temp = Mage::getStoreConfig('shipping/origin/address3', $this->getStore())) {
            $shipperStreetLines[] = $temp;
        }

        $request['RequestedShipment']['Shipper'] = array('Address' => array(
            'StreetLines' => $shipperStreetLines,
            'City' => $r->getOrigCity(),
            'StateOrProvinceCode' => $r->getOrigRegionCode(),
            'PostalCode' => $r->getOrigPostal(),
            'CountryCode' => $r->getOrigCountry()
        ));

        $request['RequestedShipment']['Recipient'] = array('Address' => array(
            'PostalCode' => $r->getDestPostal(),
            'CountryCode' => $r->getDestCountry()
        ));
        if ($this->getConfigData('residence_delivery')) {
            $request['RequestedShipment']['Recipient']['Address']['Residential'] = true;
        }

        $request['RequestedShipment']['ShippingChargesPayment'] = $shipping_charges_payment;
        $request['RequestedShipment']['RateRequestTypes'] = 'LIST';
        $request['RequestedShipment']['TotalWeight'] = array(
            'Value' => $r->getWeight(),
            'Units' => $this->getWeightUnits()
        );
        $request['RequestedShipment']['TotalInsuredValue'] = array(
            'Amount' => $r->getValue(),
            'Currency' => $this->getCurrencyCode()
        );
        $request['RequestedShipment']['PackageCount'] = '1';
        $request['RequestedShipment']['PackageDetail'] = 'PACKAGE_SUMMARY';

        $response = $this->_getCachedQuotes(Zend_Json::encode($request));
        if ($response === null) {
            $debugData = array('request' => $request);
            try {
                $response = $client->getRates($request);

                $debugData['result'] = $response;
                $this->_setCachedQuotes(Zend_Json::encode($request), $response);
                if ($response->HighestSeverity == 'FAILURE') {
                    $response = null;
                }
            } catch (SoapFault $fault) {
                $debugData['result'] = array('error' => $fault->getMessage(), 'code' => $fault->getCode());
                $response = null;
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($response);
    }

    /* Overrides the logic of
     * @see Mage_Usa_Model_Shipping_Carrier_Fedex::_parseXmlResponse()
     * by using the FedEx SOAP APIs
     */
    protected function _parseXmlResponse($response)
    {
        $costArr = array();
        $priceArr = array();

        if ($response == null) {
            $errorTitle = 'Unknown error';
        } elseif ($response->HighestSeverity == 'ERROR') {
            $errorTitle = '';
            if (is_array($response->Notifications)) {
                foreach ($response->Notifications as $notification) {
                    $errorTitle .= $notification->Severity . ': ' . $notification->Message . "\n";
                }
            } else {
                $errorTitle .= $response->Notifications->Severity . ': ' . $response->Notifications->Message . "\n";
            }
        } else {
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

            foreach ($response->RateReplyDetails as $rateReply) {
                if (in_array((string)$rateReply->ServiceType, $allowedMethods)) {
                    $_serviceType = (string)$rateReply->ServiceType;
                    $costArr[$_serviceType] = (string)$rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
                    $priceArr[$_serviceType] = $this->getMethodPrice($costArr[$_serviceType], $_serviceType);
                }
            }

            asort($priceArr);
        }

        $result = Mage::getModel('shipping/rate_result');
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            //$error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('fedex');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getCode('method', $method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        return $result;
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

    public function getAddressValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        return '';
    }


    /********************************************************************************************************************
     * New functions created for shipment functionality
     ********************************************************************************************************************/
    public function setShipRequest(Unl_Ship_Model_Shipment_Request $request) {
        $this->_shiprequest = $request;
        return $this;
    }

    /**
     * Creates a shipment to be sent. Initializes the shipment, retrieves tracking number, and creates shipping label.
     *
     * @return array An array of Unl_Ship_Model_Shipment_Package objects. An exception should be thrown on error.
     */
    public function createShipment(Unl_Ship_Model_Shipment_Request $request) {
        $this->setShipRequest($request);
        $this->_result = $this->_createShipment();

        return $this->_result;
    }

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
