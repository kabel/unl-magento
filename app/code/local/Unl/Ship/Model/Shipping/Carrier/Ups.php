<?php

class Unl_Ship_Model_Shipping_Carrier_Ups
    extends Mage_Usa_Model_Shipping_Carrier_Ups
    implements Unl_Ship_Model_Shipping_Carrier_Xmlship_Interface
{
    protected $_shiprequest;

    // see UPS Developer Guide for infomartion about error codes
    protected $_retryErrors = array(
        120203,
        120213,
        120217
    );

    protected $_errorCodes = array();

    public function addErrorRetry($code)
    {
        $this->_errorCodes[] = $code;
    }

    public function isErrorRetried($code)
    {
        return in_array($code, $this->_errorCodes);
    }

    public function isRequestRetryAble($code)
    {
        if (in_array($code, $this->_retryErrors)) {
            return true;
        }

        return false;
    }

    protected function _getCleanXmlValue($code, $value)
    {
        if ($code == 'shipto_addr2') {
            if ($this->isErrorRetried(120203)) {
                $value = '';
            }
        } else if ($code == 'shipto_phone') {
            $value = preg_replace('/[^\d]/', '', $value);
            if ($this->isErrorRetried(120213) || $this->isErrorRetried(120217)) {
                $value = '';
            }
        }

        return $value;
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

    //
    // FROM MAGENTO 1.5 Mage_Usa_Model_Shipping_Carrier_Abstract
    const GUAM_COUNTRY_ID = 'GU';
    const GUAM_REGION_CODE = 'GU';

    protected static $_quotesCache = array();

    /**
     * Returns cache key for some request to carrier quotes service
     *
     * @param string|array $requestParams
     * @return string
     */
    protected function _getQuotesCacheKey($requestParams)
    {
        if (is_array($requestParams)) {
            $requestParams = implode(',', array_merge(array($this->getCarrierCode()), array_keys($requestParams), $requestParams));
        }
        return crc32($requestParams);
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        return isset(self::$_quotesCache[$key]) ? self::$_quotesCache[$key] : null;
    }

    /**
     * Sets received carrier quotes to cache
     *
     * @param string|array $requestParams
     * @param string $response
     * @return Mage_Usa_Model_Shipping_Carrier_Abstract
     */
    protected function _setCachedQuotes($requestParams, $response)
    {
        $key = $this->_getQuotesCacheKey($requestParams);
        self::$_quotesCache[$key] = $response;
        return $this;
    }

    // FROM 1.5 Mage_Usa_Model_Shipping_Carrier_Ups

    /**
     * Base currency rate
     *
     * @var double
     */
    protected $_baseCurrencyRate;


    protected function _getCgiQuotes()
    {
        $r = $this->_rawRequest;

        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            '19_destPostal'  => 'US' == $r->getDestCountry() ? substr($r->getDestPostal(), 0, 5) : $r->getDestPostal(), // UPS returns error for zip+4 US codes
            '22_destCountry' => $r->getDestCountry(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
            'weight_std'     => strtolower($r->getUnitMeasure()),
        );
        $params['47_rate_chart'] = $params['47_rate_chart']['label'];

        $responseBody = $this->_getCachedQuotes($params);
        if ($responseBody === null) {
            $debugData = array('request' => $params);
            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultCgiGatewayUrl;
                }
                $client = new Zend_Http_Client();
                $client->setUri($url);
                $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
                $client->setParameterGet($params);
                $response = $client->request();
                $responseBody = $response->getBody();

                $debugData['result'] = $responseBody;
                $this->_setCachedQuotes($params, $responseBody);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $responseBody = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseCgiResponse($responseBody);
    }

    protected function _getXmlQuotes()
    {
        $url = $this->getConfigData('gateway_xml_url');

        $this->setXMLAccessRequest();
        $xmlRequest=$this->_xmlAccessRequest;

        $r = $this->_rawRequest;
        $params = array(
            'accept_UPS_license_agreement' => 'yes',
            '10_action'      => $r->getAction(),
            '13_product'     => $r->getProduct(),
            '14_origCountry' => $r->getOrigCountry(),
            '15_origPostal'  => $r->getOrigPostal(),
            'origCity'       => $r->getOrigCity(),
            'origRegionCode' => $r->getOrigRegionCode(),
            '19_destPostal'  => 'US' == $r->getDestCountry() ? substr($r->getDestPostal(), 0, 5) : $r->getDestPostal(), // UPS returns error for zip+4 US codes
            '22_destCountry' => $r->getDestCountry(),
            'destRegionCode' => $r->getDestRegionCode(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );

        if ($params['10_action'] == '4') {
            $params['10_action'] = 'Shop';
            $serviceCode = null; // Service code is not relevant when we're asking ALL possible services' rates
        } else {
            $params['10_action'] = 'Rate';
            $serviceCode = $r->getProduct() ? $r->getProduct() : '';
        }

        if ($r->getValue() >= 1000) { // Require insurance for >= $1000
            $params['insurance_value'] = $r->getValue();
            $params['insurance_currencycode'] = $this->_request->getBaseCurrency()->getCurrencyCode();
        }
        $serviceDescription = $serviceCode ? $this->getShipmentByCode($serviceCode) : '';

$xmlRequest .= <<< XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <PickupType>
          <Code>{$params['47_rate_chart']['code']}</Code>
          <Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>
XMLRequest;

        if ($serviceCode !== null) {
            $xmlRequest .= "<Service>" .
                "<Code>{$serviceCode}</Code>" .
                "<Description>{$serviceDescription}</Description>" .
                "</Service>";
        }

      $xmlRequest .= <<< XMLRequest
      <Shipper>
XMLRequest;

        if ($this->getConfigFlag('negotiated_active') && ($shipper = $this->getConfigData('shipper_number')) ) {
            $xmlRequest .= "<ShipperNumber>{$shipper}</ShipperNumber>";
        }

$xmlRequest .= <<< XMLRequest
      <Address>
          <City>{$params['origCity']}</City>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

          $xmlRequest .= ($params['49_residential']==='01' ? "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>" : '');

$xmlRequest .= <<< XMLRequest
      </Address>
    </ShipTo>


    <ShipFrom>
      <Address>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType><Code>{$params['48_container']}</Code></PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$r->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
XMLRequest;
        //if insurance requested
        if (!empty($params['insurance_value'])) {
            $xmlRequest .= '
      <PackageServiceOptions>
          <InsuredValue>
               <CurrencyCode>'.$params['insurance_currencycode'].'</CurrencyCode>
               <MonetaryValue>'.$params['insurance_value'].'</MonetaryValue>
          </InsuredValue>
      </PackageServiceOptions>';
        }

$xmlRequest .= <<< XMLRequest
    </Package>
XMLRequest;
        if ($this->getConfigFlag('negotiated_active')) {
            $xmlRequest .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }

$xmlRequest .= <<< XMLRequest
  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;


        $xmlResponse = $this->_getCachedQuotes($xmlRequest);
        if ($xmlResponse === null) {
            $debugData = array('request' => $xmlRequest);
            try {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
                $xmlResponse = curl_exec ($ch);

                $debugData['result'] = $xmlResponse;
                $this->_setCachedQuotes($xmlRequest, $xmlResponse);
            }
            catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $xmlResponse = '';
            }
            $this->_debug($debugData);
        }

        return $this->_parseXmlResponse($xmlResponse);
    }

    /**
     * Get base currency rate
     *
     * @param string $code
     * @return double
     */
    protected function _getBaseCurrencyRate($code)
    {
        if (!$this->_baseCurrencyRate) {
            $this->_baseCurrencyRate = Mage::getModel('directory/currency')
                ->load($code)
                ->getAnyRate($this->_request->getBaseCurrency()->getCode());
        }

        return $this->_baseCurrencyRate;
    }

    protected function _parseXmlResponse($xmlResponse)
    {
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($xmlResponse))>0) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if ($success===1) {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $this->getConfigFlag('negotiated_active')
                    && $this->getConfigData('shipper_number')
                    && !empty($negotiatedArr);

                $allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();

                foreach ($arr as $shipElement){
                    $code = (string)$shipElement->Service->Code;
                    #$shipment = $this->getShipmentByCode($code);
                    if (in_array($code, $allowedMethods)) {

                        if ($negotiatedActive) {
                            $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        } else {
                            $cost = $shipElement->TotalCharges->MonetaryValue;
                        }

                        //convert price with Origin country currency code to base currency code
                        $successConversion = true;
                        $responseCurrencyCode = (string) $shipElement->TotalCharges->CurrencyCode;
                        if ($responseCurrencyCode) {
                            if (in_array($responseCurrencyCode, $allowedCurrencies)) {
                                $cost *= $this->_getBaseCurrencyRate($responseCurrencyCode);
                            } else {
                                $errorTitle = Mage::helper('directory')
                                    ->__('Can\'t convert rate from "%s-%s".',
                                        $responseCurrencyCode,
                                        $this->_request->getPackageCurrency()->getCode());
                                $error = Mage::getModel('shipping/rate_result_error');
                                $error->setCarrier('ups');
                                $error->setCarrierTitle($this->getConfigData('title'));
                                $error->setErrorMessage($errorTitle);
                                $successConversion = false;
                            }
                        }

                        if ($successConversion) {
                            $costArr[$code] = $cost;
                            $priceArr[$code] = $this->getMethodPrice(floatval($cost),$code);
                        }
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier('ups');
                $error->setCarrierTitle($this->getConfigData('title'));
                Mage::log($errorTitle);
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            if (!isset($errorTitle)){
                $errorTitle = Mage::helper('usa')->__('Cannot retrieve shipping rates');
            }
            Mage::log($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getShipmentByCode($method);
                $rate->setMethodTitle($method_arr);
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
        return $result;
    }
    // END
    //

    protected function _parseXmlTrackingResponse($trackingvalue, $xmlResponse)
    {
        $errorTitle = 'Unable to retrieve tracking';
        $resultArr = array();
        $packageProgress = array();

        if ($xmlResponse) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//TrackResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];

            if($success===1){
                $arr = $xml->getXpath("//TrackResponse/Shipment/Service/Description/text()");
                $resultArr['service'] = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/PickupDate/text()");
                $date = (string)$arr[0];
                $resultArr['shipped_date'] = implode('-', array(substr($date,0,4), substr($date,4,2), substr($date,-2,2)));

                $arr = $xml->getXpath("//TrackResponse/Shipment/ScheduledDeliveryDate/text()");
                if ($arr) {
                    $date = (string)$arr[0];
                    $resultArr['estimateddate'] = implode('-', array(substr($date,0,4), substr($date,4,2), substr($date,-2,2)));
                }

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/Weight/text()");
                $weight = (string)$arr[0];

                $arr = $xml->getXpath("//TrackResponse/Shipment/Package/PackageWeight/UnitOfMeasurement/Code/text()");
                $unit = (string)$arr[0];

                $resultArr['weight'] = "{$weight} {$unit}";

                $activityTags = $xml->getXpath("//TrackResponse/Shipment/Package/Activity");
                if ($activityTags) {
                    $i=1;
                    foreach ($activityTags as $activityTag) {
                        $addArr=array();
                        if (isset($activityTag->ActivityLocation->Address->City)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->City;
                        }
                        if (isset($activityTag->ActivityLocation->Address->StateProvinceCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->StateProvinceCode;
                        }
                        if (isset($activityTag->ActivityLocation->Address->CountryCode)) {
                            $addArr[] = (string)$activityTag->ActivityLocation->Address->CountryCode;
                        }
                        $dateArr = array();
                        $date = (string)$activityTag->Date;//YYYYMMDD
                        $dateArr[] = substr($date,0,4);
                        $dateArr[] = substr($date,4,2);
                        $dateArr[] = substr($date,-2,2);

                        $timeArr = array();
                        $time = (string)$activityTag->Time;//HHMMSS
                        $timeArr[] = substr($time,0,2);
                        $timeArr[] = substr($time,2,2);
                        $timeArr[] = substr($time,-2,2);

                        if ($i==1) {
                            $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                            if ((string)$activityTag->Status->StatusType->Code == 'D') {
                                $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                                $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                                $resultArr['delivery_location'] = (string)$activityTag->ActivityLocation->Description;
                                $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                                if ($addArr) {
                                    $resultArr['deliveryto']=implode(', ',$addArr);
                                }
                            }
                       }
                       $tempArr=array();
                       $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                       $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                       $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                       if ($addArr) {
                        $tempArr['deliverylocation']=implode(', ',$addArr);
                       }
                       $packageProgress[] = $tempArr;
                       $i++;
                    }
                    $resultArr['progressdetail'] = $packageProgress;
                }
            } else {
                $arr = $xml->getXpath("//TrackResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
            }
        }

        if (!$this->_result) {
            $this->_result = Mage::getModel('shipping/tracking_result');
        }

        $defaults = $this->getDefaults();

        if ($resultArr) {
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('ups');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
        } else {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle);
            $this->_result->append($error);
        }
        return $this->_result;
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
                '1DM'    => 'Next Day Air Early AM',
                '1DML'   => 'Next Day Air Early AM Letter',
                '1DA'    => 'Next Day Air',
                '1DAL'   => 'Next Day Air Letter',
                '1DAPI'  => 'Next Day Air Intra (Puerto Rico)',
                '1DP'    => 'Next Day Air Saver',
                '1DPL'   => 'Next Day Air Saver Letter',
                '2DM'    => '2nd Day Air AM',
                '2DML'   => '2nd Day Air AM Letter',
                '2DA'    => '2nd Day Air',
                '2DAL'   => '2nd Day Air Letter',
                '3DS'    => '3 Day Select',
                'GND'    => 'Ground',
                'GNDCOM' => 'Ground Commercial',
                'GNDRES' => 'Ground Residential',
                'STD'    => 'Canada Standard',
                'XPR'    => 'Worldwide Express',
                'WXS'    => 'Worldwide Express Saver',
                'XPRL'   => 'Worldwide Express Letter',
                'XDM'    => 'Worldwide Express Plus',
                'XDML'   => 'Worldwide Express Plus Letter',
                'XPD'    => 'Worldwide Expedited',
            ),

            'container'=>array(
                'CP'     => '02',
                'ULE'    => '01',
                'UT'     => '03',
                'UEB'    => '21',
                'UW25'   => '24',
                'UW10'   => '25',
                'UPAK'   => '04',
                'UEBS'   => '2a',
                'UEBM'   => '2b',
                'UEBL'   => '2c',
                'UPAL'   => '30',
            ),

            'container_description'=>array(
                'CP'     => Mage::helper('usa')->__('Customer Supplied Package'),
                'ULE'    => Mage::helper('usa')->__('UPS Letter'),
                'UT'     => Mage::helper('usa')->__('UPS Tube'),
                'UEB'    => Mage::helper('usa')->__('UPS Express Box'),
                'UW25'   => Mage::helper('usa')->__('UPS 25KG Box'),
                'UW10'   => Mage::helper('usa')->__('UPS 10KG Box'),
                'UPAK'   => Mage::helper('usa')->__('UPS PAK'),
                'UEBS'   => Mage::helper('usa')->__('UPS Small Express Box'),
                'UEBM'   => Mage::helper('usa')->__('UPS Medium Express Box'),
                'UEBL'   => Mage::helper('usa')->__('UPS Large Express Box'),
                'UPAL'   => Mage::helper('usa')->__('Pallet'),
            ),

            //these are dimensions in centimeters for each package type
            'container_dimensions_cm' => array(
                //Customer Supplied Package
                'CP' => $cdef_cm,
                //UPS Letter
                'ULE' => array(
                    'height' => 0,
                    'width' => 9.5 * 2.54,
                    'length' => 12.5 * 2.54,
                ),
                //Tube
                'UT' => array(
                    'height' => 38 * 2.54,
                    'width' => 6 * 2.54,
                    'length' => 6 * 2.54,
                ),
                //PAK
                'UPAK' => array(
                    'height' => 0,
                    'width' => 12.75 * 2.54,
                    'length' => 16 * 2.54,
                ),
                //UPS Express Box (defining same as small)
                'UEB' => array(
                    'height' => 2 * 2.54,
                    'width' => 11 * 2.54,
                    'length' => 13 * 2.54,
                ),
                //UPS Small Express Box
                'UEBS' => array(
                    'height' => 2 * 2.54,
                    'width' => 11 * 2.54,
                    'length' => 13 * 2.54,
                ),
                //UPS Medium Express Box
                'UEBM' => array(
                    'height' => 3 * 2.54,
                    'width' => 11 * 2.54,
                    'length' => 15 * 2.54,
                ),
                //UPS Large Express Box
                'UEBL' => array(
                    'height' => 3 * 2.54,
                    'width' => 13 * 2.54,
                    'length' => 18 * 2.54,
                ),
                //UPS 25KG Box
                'UW25' => array(
                    'height' => 14 * 2.54,
                    'width' => 17.38 * 2.54,
                    'length' => 19.38 * 2.54,
                ),
                //UPS 10KG Box
                'UW10' => array(
                    'height' => 10.75 * 2.54,
                    'width' => 13.25 * 2.54,
                    'length' => 16.5 * 2.54,
                ),
                //Pallet
                'UPAL' => array(
                    'height' => 120,
                    'width' => 160,
                    'length' => 200,
                ),
            ),

            //these are dimensions in inches for each package type
            'container_dimensions_in' => array(
                //Customer Supplied Package
                'CP' => $cdef_in,
                //UPS Letter
                'ULE' => array(
                    'height' => 0,
                    'width' => 9.5,
                    'length' => 12.5,
                ),
                //Tube
                'UT' => array(
                    'height' => 6,
                    'width' => 6,
                    'length' => 38,
                ),
                //PAK
                'UPAK' => array(
                    'height' => 0,
                    'width' => 12.75,
                    'length' => 16,
                ),
                //UPS Express Box (defining same as small)
                'UEB' => array(
                    'height' => 2,
                    'width' => 11,
                    'length' => 13,
                ),
                //UPS Small Express Box
                'UEBS' => array(
                    'height' => 2,
                    'width' => 11,
                    'length' => 13,
                ),
                //UPS Medium Express Box
                'UEBM' => array(
                    'height' => 3,
                    'width' => 11,
                    'length' => 15,
                ),
                //UPS Large Express Box
                'UEBL' => array(
                    'height' => 3,
                    'width' => 13,
                    'length' => 18,
                ),
                //UPS 25KG Box
                'UW25' => array(
                    'height' => 14,
                    'width' => 17.38,
                    'length' => 19.38,
                ),
                //UPS 10KG Box
                'UW10' => array(
                    'height' => 10.75,
                    'width' => 13.25,
                    'length' => 16.5,
                ),
                //Pallet
                'UPAL' => array(
                    'height' => 47.24,
                    'width' => 62.99,
                    'length' => 78.74,
                ),
            ),

            'unit_of_dimension'=>array(
                'IN'   =>  Mage::helper('usa')->__('Inches'),
                'CM'   =>  Mage::helper('usa')->__('Centimeters'),
            ),

        );

        if (!isset($codes[$type])) {
            return parent::getCode($type, $code);
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
           return parent::getCode($type, $code);
        } else {
            return $codes[$type][$code];
        }
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        $allowed = explode(',', $this->getConfigData('allowed_methods'));
        $arr = array();
        $isByCode = $this->getConfigData('type') == 'UPS_XML';
        foreach ($allowed as $k) {
            $arr[$k] = $isByCode ? $this->getShipmentByCode($k) : $this->getCode('method', $k);
        }
        return $arr;
    }

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

    /****************************** New Methods for Shipping *************************/

    /**
     * Creates a shipment to be sent. Will initialize the shipment, retrieve tracking number, and get shipping label
     *
     */
    public function createShipment(Unl_Ship_Model_Shipment_Request $request) {
        $this->_shiprequest = $request;
        $this->_result = $this->_createShipment();
        return $this->_result;
    }

    protected function _createShipment() {
        $store = $this->getStore();
        $orderStore = $this->_shiprequest->getOrder()->getStore();
        $this->setStore($orderStore);

        switch ($this->getConfigData('type')) {
            case 'UPS':
                $this->setStore($store);
                throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('UPS shipments can only be automatically created when \'United Parcel Service XML\' is configured as the \'UPS type\'.'));
                return false;
            //currently only available through XML
            case 'UPS_XML':
                return $this->_createXmlShipment();
        }
        $this->setStore($store);
        return null;
    }

    /**
     * Generates and executes the actual XML requests to forward a shipment, which consists of 4 separate steps:
     *
     *     1. ShipmentConfirmRequest - submission of request for shipment containing proposed shipment information
     *     2. ShipmentConfirmResponse - data sent back containing rate information for the proposed shipment
     *     3. ShipmentAcceptRequest - submission indicating acceptance of proposed rate for shipment
     *     4. ShipmentAcceptResponse - data sent back containing tracking info and shipping label
     *
     * @return array An array of Unl_Ship_Model_Shipment_Package objects. An exception will be thrown on error.
     */
    protected function _createXmlShipment() {
        /* @var $order Mage_Sales_Model_Order */
        $order = $this->_shiprequest->getOrder();

        //get the params
        $url = $this->getConfigData('shipping_xml_url');
        //make sure the $url does not have ShipConfirm or ShipAccept on the end
        $url = (rtrim($url, 'ShipConfirm'));
        $url = (rtrim($url, 'ShipAccept'));
        $url = (rtrim($url, '/'));

        $packages = $this->_shiprequest->getPackages();

        $retpak = array();
        try {
            //only send one package per request so that we can track the price of individual packages
            foreach ($packages as $reqpackage) {
                //ShipmentConfirmRequest
                $ship_confirm_response = $this->_sendXmlShipmentConfirmRequest($url.'/ShipConfirm', $order, array($reqpackage));

                //ShipmentAcceptRequest
                $ship_accept_response = $this->_sendXmlShipmentAcceptRequest($url.'/ShipAccept', $order, $ship_confirm_response->getShipmentDigest());

                //store the results of the request if it was successful
                $respackage = current($ship_accept_response->getPackages());

                //create a package to store
                $pkg = Mage::getModel('unl_ship/shipment_package')
                    ->setOrderId($order->getId())
                    ->setCarrier($this->getCarrierCode())
                    ->setCarrierShipmentId($ship_accept_response->getShipmentIdentificationNumber())
                    ->setWeightUnits($ship_accept_response->getBillingWeightUnits())
                    ->setWeight($ship_accept_response->getBillingWeight())
                    ->setTrackingNumber($respackage['tracking_number'])
                    ->setCurrencyUnits($ship_accept_response->getCurrencyUnits())
                    ->setTransportationCharge($ship_accept_response->getTransportationShippingCharges())
                    ->setServiceOptionCharge($ship_accept_response->getServiceOptionsShippingCharges())
                    ->setShippingTotal($ship_accept_response->getTotalShippingCharges())
                    ->setNegotiatedTotal($ship_accept_response->getNegotiatedTotalShippingCharges())
                    ->setLabelFormat($respackage['label_image_format'])
                    ->setLabelImage($respackage['label_image'])
                    ->setHtmlLabelImage($respackage['html_image'])
                    ->setInsDoc($ship_accept_response->getInsDoc())
                    ->setIntlDoc($ship_accept_response->getIntlDoc())
                    ->setDateShipped(now());

                $retpak[$reqpackage->getPackageIndex()] = $pkg;
            }
        } catch (Mage_Shipping_Exception $e) {
            if (empty($retpak)) {
                throw $e;
            } else {
                $retpak[$reqpackage->getPackageIndex()] = $e->getMessage();
            }
        }

        return $retpak;
    }

    /**
     * Sends the ShipmentConfirmRequest message.
     *
     * @param Mage_Sales_Model_Order $order The order to ship from
     * @param array $packages An array of Unl_Ship_Model_Shipment_Package objects.
     * @param string $url URL to submit request to.
     * @return Mage_Shipping_Model_Shipment_Confirmation The ShipmentConfirmationResponse object
     */
    protected function _sendXmlShipmentConfirmRequest($url, $order, $packages)
    {
        if (empty($url)) {
            $url = rtrim($this->getConfigData('shipping_xml_url'), '/').'/ShipConfirm';
        }

        $store = $order->getStore();

        $shipaddress = $order->getShippingAddress();  //Mage_Sales_Model_Order_Address
        if (empty($shipaddress)) {
            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('No shipping address for order.'));
            return false;
        }

        //get the package data
        $pkgs = array();
        $invoicetotal = 0;
        $invoicetotalpkgtype = true;
        foreach ($packages as $pkg) {
            $p = array(
                'package_code' => $this->getCode('container', $pkg->getContainerCode()),  //2 char, code for packaging type, default is '02' - customer supplied package
                'package_description' => $this->getCode('package_type', $pkg->getContainerCode()),  //description of packaging, default 'customer supplied package
                'weight_unitcode' => $this->getWeightUnits(),  //units for weight, can be either 'LBS' or 'KGS'
                'weight' => sprintf("%01.1f", $pkg->getWeight()),  //5 chars, up to 1 digit after decimal
                'dimension_unitcode' => $this->getDimensionUnits(),  //units for dimension, can be either 'IN' or 'CM'
                'height' => $pkg->getHeight(),
                'width' => $pkg->getWidth(),
                'length' => $pkg->getLength(),
                'reference_code' => 'TN',  //2 chars, optional - default 'TN' (Transaction Reference Number) - Order ID
                'reference_value' => substr($order->getRealOrderId(), 0, 35), //35 chars, optional - order_id
            );

            //reference codes can only be used in US domestic shipments
            if ($shipaddress->getCountryId() != 'US' || $store->getConfig('shipping/origin/country_id') != 'US') {
                unset($p['reference_code']);
                unset($p['reference_value']);
            }

            //if package has confirmation
            if ($pkg->getConfirmationCode() != null) {
                $p['confirmation_type'] = $pkg->getConfirmationCode();  //'1' (delivery confirmation), '2' (signature required), or '3' (adult signature required)
                $p['confirmation_number'] = $pkg->getConfirmationNumber();  //delivery confirmation control number
            }
            //if package insured
            if ($pkg->getInsuranceCode() != null) {
                $p['insurance_type'] = $pkg->getInsuranceCode();  //2 char, '01' (EVS Declared Value), or '02' (DVS Shipper Declared Value), UPS default is '01'
                $p['insurance_currencycode'] = $pkg->getInsuranceCurrencyCode();  //3 letter currency abbreviation (USD)
                $p['insurance_value'] = $pkg->getInsuranceValue();  //limited to 99999999.99 with up to 2 digits after
            } else if ($pkg->getValue() >= 1000) {  //force declared value on packages
                $p['insurance_type'] = '01';
                $p['insurance_currencycode'] = 'USD';
                $p['insurance_value'] = sprintf('%01.2f', $pkg->getValue());
            }

            //if release without signature requested
            if ($pkg->getReleaseWithoutSignature()) {
                $p['release_without_signature'] = 1;  //if non-null driver may release package without a signature, only valid in US & Puerto Rico
            }

            //if verbal confirmation requested
            if ($pkg->getVerballyConfirm()) {
                $p['verbal_name'] = substr($store->getConfig('shipping/origin/attension'), 0, 35);  //35 char, contact name to notify on delivery
                $p['verbal_phone'] = substr($store->getConfig('shipping/origin/phone'), 0, 15);  //required for international destinations
            }
            $pkgs[] = $p;

            //add the package items to the invoice total
            $invoicetotal += $pkg->getValue();
            if ($p['package_code'] == '01') {
                $invoicetotalpkgtype = false;
            }
        }

        $ship = explode('_', $order->getShippingMethod());
        $servicecode = $ship[1];

        $params = array(
            'order_id' => $order->getRealOrderId(),
            'address_validation' =>  'validate',
            'shipment_description' => 'Order # '. $order->getRealOrderId(),  //35 chars, required for some international, optional
            'shipper_name' => substr($store->getWebsite()->getName(), 0, 35),  //35 chars
            'shipper_attention' => substr($store->getConfig('shipping/origin/attention'), 0, 35),  //35 chars required for international and next day AM
            'shipper_number' => $this->getConfigData('shipper_number'),  //6 digit UPS account number
            'shipper_phone' => $store->getConfig('shipping/origin/phone'),  //15 chars, digits only, required for international, optional
            'shipper_addr1' => $store->getConfig('shipping/origin/address1'),  //35 chars
            'shipper_addr2' => $store->getConfig('shipping/origin/address2'),  //35 chars
            'shipper_addr3' => $store->getConfig('shipping/origin/address3'),  //35 chars
            'shipper_city' => $store->getConfig('shipping/origin/city'),  //30 chars
            'shipper_state' => Mage::getModel('directory/region')->load($store->getConfig('shipping/origin/region_id'))->getCode(),  //2-5 chars, required for US, Mexico, and Canada (for Ireland use 5 digit county abbreviation)
            'shipper_postalcode' => $store->getConfig('shipping/origin/postcode'),  //9 chars , required for US, Canada, Puerto Rico, may include - with 9 digits
            'shipper_country' => $store->getConfig('shipping/origin/country_id'),  //2 digit ISO code

            'shipto_name' => $shipaddress->getCompany() ? substr($shipaddress->getCompany(), 0, 35) : substr($shipaddress->getName(), 0, 35),  //35 chars
            'shipto_attention' => $shipaddress->getCompany() ? substr($shipaddress->getName(), 0, 35) : '',  //35 chars, required for international and next day AM
            'shipto_phone' => $this->_getCleanXmlValue('shipto_phone', $shipaddress->getTelephone()),  //15 chars, digits only, required for international, optional
            'shipto_addr1' => $shipaddress->getStreet(1),  //35 chars
            'shipto_addr2' => $this->_getCleanXmlValue('shipto_addr2', $shipaddress->getStreet(2)),  //35 chars
            'shipto_addr3' => $shipaddress->getStreet(3),  //35 chars
            'shipto_city' => $shipaddress->getCity(),  //30 chars
            'shipto_state' => $shipaddress->getRegionCode(),  //2-5 chars, required for US, Mexico, and Canada (for Ireland use 5 digit county abbreviation)
            'shipto_postalcode' => $shipaddress->getPostcode(),  //9 chars , required for US, Canada, Puerto Rico, may include - with 9 digits
            'shipto_country' => $shipaddress->getCountryId(),  //2 digit ISO code
            'negotiated_rate' => $this->getConfigFlag('negotiated_active'),

            'service_code' => $servicecode,  //UPS shipment service code
            'service_description' => $this->getShipmentByCode($servicecode),  //UPS shipment service description, optional
            'invoice_line_total_currency_code' => Mage::app()->getBaseCurrencyCode(),
            'invoice_line_total_value' => round($invoicetotal),
            'invoice_total_pkg_type' => $invoicetotalpkgtype,

            'packages' => $pkgs,  //package information
            'label_printcode' => 'GIF',
            'label_agent' => 'Mozilla/4.5',
            'label_imagecode' => 'GIF',

            'thirdparty_number' => $this->getConfigData('third_party_account_number'),  //6 digit UPS account number
            'thirdparty_postalcode' => $this->getConfigData('third_party_postcode'),  //required for US, Canada, Puerto Rico, 9 digits plus -
            'thirdparty_country' => $this->getConfigData('third_party_country'),  //2 digit ISO code

            'destination_type' => $this->getConfigData('dest_type'),  //RES=Residential, COM=Commercial
        );

        //TODO: Use address validation to determine destination_type

        //start with the access request
        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest;

        $xmlRequest .= <<< XMLRequest

<?xml version="1.0"?>
<ShipmentConfirmRequest>
    <Request>
        <TransactionReference>
            <CustomerContext>{$params['order_id']}</CustomerContext>
            <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>ShipConfirm</RequestAction>
        <RequestOption>{$params['address_validation']}</RequestOption>
    </Request>
    <Shipment>
        <Description><![CDATA[{$params['shipment_description']}]]></Description>
        <Shipper>
            <Name><![CDATA[{$params['shipper_name']}]]></Name>
            <AttentionName><![CDATA[{$params['shipper_attention']}]]></AttentionName>
            <ShipperNumber><![CDATA[{$params['shipper_number']}]]></ShipperNumber>
            <PhoneNumber><![CDATA[{$params['shipper_phone']}]]></PhoneNumber>
            <Address>
                 <AddressLine1><![CDATA[{$params['shipper_addr1']}]]></AddressLine1>
                 <AddressLine2><![CDATA[{$params['shipper_addr2']}]]></AddressLine2>
                 <AddressLine3><![CDATA[{$params['shipper_addr3']}]]></AddressLine3>
                 <City><![CDATA[{$params['shipper_city']}]]></City>
                 <StateProvinceCode><![CDATA[{$params['shipper_state']}]]></StateProvinceCode>
                 <PostalCode><![CDATA[{$params['shipper_postalcode']}]]></PostalCode>
                 <CountryCode><![CDATA[{$params['shipper_country']}]]></CountryCode>
            </Address>
        </Shipper>
        <ShipTo>
            <CompanyName><![CDATA[{$params['shipto_name']}]]></CompanyName>
XMLRequest;

        //determine if residential
        if (!empty($params['shipto_attention'])) {
            $xmlRequest .= <<< XMLRequest

            <AttentionName><![CDATA[{$params['shipto_attention']}]]></AttentionName>
XMLRequest;
        }

        $xmlRequest .= <<< XMLRequest

            <PhoneNumber><![CDATA[{$params['shipto_phone']}]]></PhoneNumber>
            <Address>
                 <AddressLine1><![CDATA[{$params['shipto_addr1']}]]></AddressLine1>
                 <AddressLine2><![CDATA[{$params['shipto_addr2']}]]></AddressLine2>
                 <AddressLine3><![CDATA[{$params['shipto_addr3']}]]></AddressLine3>
                 <City><![CDATA[{$params['shipto_city']}]]></City>
                 <StateProvinceCode><![CDATA[{$params['shipto_state']}]]></StateProvinceCode>
                 <PostalCode><![CDATA[{$params['shipto_postalcode']}]]></PostalCode>
                 <CountryCode><![CDATA[{$params['shipto_country']}]]></CountryCode>
XMLRequest;

        //determine if residential
        if ($params['destination_type'] == 'RES') {
            $xmlRequest .= <<< XMLRequest

                 <ResidentialAddress />
XMLRequest;
        }

        $xmlRequest .= <<< XMLRequest

            </Address>
        </ShipTo>
XMLRequest;

        //if negotiated rate should be retrieved
        if ($params['negotiated_rate']) {
            $xmlRequest .= <<< XMLRequest

        <RateInformation>
            <NegotiatedRatesIndicator/>
        </RateInformation>
XMLRequest;
        }
        $xmlRequest .= <<< XMLRequest

        <Service>
            <Code>{$params['service_code']}</Code>
            <Description><![CDATA[{$params['service_description']}]]></Description>
        </Service>
XMLRequest;

        //Determine if the invoice line total should be added
        //This is the case if shipper is in US and destination is in CA or PR and package types are not UPS Letter
        if ($params['invoice_total_pkg_type'] && ($params['shipper_country'] == 'US') && (($params['shipto_country'] == 'CA') ||
          ($params['shipto_country'] == 'US' && $params['shipto_state'] == 'PR'))) {
              $xmlRequest .= <<< XMLRequest

        <InvoiceLineTotal>
            <CurrencyCode>{$params['invoice_line_total_currency_code']}</CurrencyCode>
            <MonetaryValue>{$params['invoice_line_total_value']}</MonetaryValue>
        </InvoiceLineTotal>
XMLRequest;
        }

        //Determine the payment method to be used
        //third party account (different from shipper)
        if ($this->getConfigFlag('third_party') && !empty($params['thirdparty_number'])) {
            $xmlRequest .= <<< XMLRequest

        <PaymentInformation>
             <BillThirdParty>
                  <BillThirdPartyShipper>
                          <AccountNumber>{$params['thirdparty_number']}</AccountNumber>
                          <ThirdParty>
                              <Address>
                                  <PostalCode>{$params['thirdparty_postalcode']}</PostalCode>
                                  <CountryCode>{$params['thirdparty_country']}</CountryCode>
                              </Address>
                          </ThirdParty>
                  </BillThirdPartyShipper>
             </BillThirdParty>
        </PaymentInformation>
XMLRequest;
        }

        //pay with the shippers account
        else  {
            $xmlRequest .= <<< XMLRequest

        <PaymentInformation>
            <Prepaid>
                 <BillShipper>
                      <AccountNumber>{$params['shipper_number']}</AccountNumber>
                 </BillShipper>
            </Prepaid>
        </PaymentInformation>
XMLRequest;
        }

        //Add each package
        foreach ($params['packages'] as $pkg) {
            $xmlRequest .= <<< XMLRequest

        <Package>
            <Description><![CDATA[{$pkg['description']}]]></Description>
            <PackagingType>
                  <Code>{$pkg['package_code']}</Code>
                  <Description><![CDATA[{$pkg['package_description']}]]></Description>
            </PackagingType>
            <Dimensions>
                <UnitOfMeasure>
                    <Code>{$pkg['dimension_unitcode']}</Code>
                </UnitOfMeasure>
                <Length>{$pkg['length']}</Length>
                <Width>{$pkg['width']}</Width>
                <Height>{$pkg['height']}</Height>
            </Dimensions>
            <PackageWeight>
                  <UnitOfMeasurement>
                      <Code>{$pkg['weight_unitcode']}</Code>
                  </UnitOfMeasurement>
                  <Weight>{$pkg['weight']}</Weight>
            </PackageWeight>
XMLRequest;

        if (!empty($pkg['reference_code'])) {
            $xmlRequest .= <<< XMLRequest
            <ReferenceNumber>
                  <Code>{$pkg['reference_code']}</Code>
                  <Value>{$pkg['reference_value']}</Value>
            </ReferenceNumber>
XMLRequest;
        }

            //add any applicable Service Options
            if (!empty($pkg['confirmation_type']) || !empty($pkg['insurance_type']) || !empty($pkg['verbal_name'])) {
                $xmlRequest .= '
            <PackageServiceOptions>';

                //if confirmation requested
                if (!empty($pkg['confirmation_type'])) {
                    $xmlRequest .= '
                  <DeliveryConfirmation>
                       <DCISType>'.$pkg['confirmation_type'].'</DCISType>
                       <DCISNumber>'.$pkg['confirmation_number'].'</DCISNumber>
                  </DeliveryConfirmation>';
                }

                //if insurance requested
                if (!empty($pkg['insurance_type'])) {
                    $xmlRequest .= '
                  <InsuredValue>
                         <Type>
                               <Code>'.$pkg['insurance_type'].'</Code>
                         </Type>
                       <CurrencyCode>'.$pkg['insurance_currencycode'].'</CurrencyCode>
                       <MonetaryValue>'.$pkg['insurance_value'].'</MonetaryValue>
                  </InsuredValue>';
                }

                //if verbal confirmation requested
                if (!empty($pkg['verbal_name'])) {
                    $xmlRequest .= '
                  <VerbalConfirmation>
                         <ContactInfo>
                             <Name><![CDATA['.$pkg['verbal_name'].']]></Name>
                             <PhoneNumber><![CDATA['.$pkg['verbal_phone'].']]></PhoneNumber>
                       </ContactInfo>
                  </VerbalConfirmation>';
                }

                //if release without signature is set
                if (!empty($pkg['release_without_signature'])) {
                    $xmlRequest .= '
                  <ShipperReleaseIndicator/>';
                }

                //close service options
                $xmlRequest .= '
            </PackageServiceOptions>';
            }
            //close package
            $xmlRequest .= '
        </Package>';
        }

        //continue
        $xmlRequest .= <<< XMLRequest

    </Shipment>
    <LabelSpecification>
        <LabelPrintMethod>
            <Code>{$params['label_printcode']}</Code>
        </LabelPrintMethod>
        <HTTPUserAgent><![CDATA[{$params['label_agent']}]]></HTTPUserAgent>
        <LabelImageFormat>
            <Code>{$params['label_imagecode']}</Code>
        </LabelImageFormat>
    </LabelSpecification>
</ShipmentConfirmRequest>
XMLRequest;

        $debugData = array('request' => $xmlRequest);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec ($ch);
            $debugData['result'] = $xmlResponse;
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }
        $this->_debug($debugData);

        return $this->_parseXmlShipmentConfirmResponse($xmlResponse, $params['order_id']);
    }

    /**
     * Parses the XML from a ShipmentConfirmReponse document and returns the results
     *
     * @param string $xmlResponse The full ShipmentConfirmResponse XML document
     * @param string $transaction_reference The reference id supplied in the initial ShipmentConfirmRequest.
     * @return Mage_Shipping_Model_Shipment_Confirmation The ShipmentConfirmationResponse object
     * @throws Unl_Ship_Exception
     */
    protected function _parseXmlShipmentConfirmResponse($xmlResponse, $transaction_reference) {
        //create the result and set the raw response
        $result = Mage::getModel('unl_ship/shipment_confirmation');

        if (!$result->setRawResponse($xmlResponse)) {
            return $result;
        }

        $success = (int)$result->getValueForXpath("//ShipmentConfirmResponse/Response/ResponseStatusCode/");
        if ($success === 1) {
            //get the transaction reference and make sure it matches what was sent
            $tref = $result->getValueForXpath("//ShipmentConfirmResponse/Response/TransactionReference/CustomerContext/");
            //if the transaction reference does not match
            if ($tref != $transaction_reference) {
                //set error and throw exception
                $errormsg = "Transaction reference '".$tref."' received in response does not match transaction reference '".$transaction_reference.
                    "' that was sent in request.";
                $result->setError($errormsg);
                return $result;
            }

            //get the currency units
            $cunits = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentCharges/TotalCharges/CurrencyCode/");
            $result->setCurrencyUnits($cunits);

            //get the shipping charges
            //transportation
            $trans = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentCharges/TransportationCharges/MonetaryValue/");
            $result->setTransportationShippingCharges($trans);
            //service options
            $servopt = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentCharges/ServiceOptionsCharges/MonetaryValue/");
            $result->setServiceOptionsShippingCharges($servopt);
            //total charges
            $total = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentCharges/TotalCharges/MonetaryValue/");
            $result->setTotalShippingCharges($total);

            //get negotiated rate total
            $negtotal = $result->getValueForXpath("//ShipmentConfirmResponse/NegotiatedRates/NetSummaryCharges/GrandTotal/MonetaryValue/");
            $result->setNegotiatedTotalShippingCharges($negtotal);

            //get billing weight
            //units
            $wunits = $result->getValueForXpath("//ShipmentConfirmResponse/BillingWeight/UnitOfMeasurement/Code/");
            $result->setBillingWeightUnits($wunits);
            //weight
            $weight = $result->getValueForXpath("//ShipmentConfirmResponse/BillingWeight/Weight/");;
            $result->setBillingWeight($weight);

            //get the shipment id number
            $shipmentid = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentIdentificationNumber/");
            $result->setShipmentIdentificationNumber($shipmentid);

            //get the shipment digest
            $shipmentdigest = $result->getValueForXpath("//ShipmentConfirmResponse/ShipmentDigest/");
            $result->setShipmentDigest($shipmentdigest);

            //make sure all required params are present
            if (empty($total) || empty($shipmentid) || empty($shipmentdigest)) {
                $errmsg = "Required parameter(s) not found in response: ";
                if (empty($total)) {
                    $errmsg .= 'TotalCharges, ';
                }
                if (empty($shipmentid)) {
                    $errmsg .= 'ShipmentIdentificationNumber, ';
                }
                if (empty($shipmentdigest)) {
                    $errmsg .= 'ShipmentDigest';
                }
                $errmsg = rtrim($errmsg, ', ');
                $result->setError($errmsg);
                return $result;
            }
        }
        else  {
            //get the error description
            $errorTitle = $result->getValueForXpath("//ShipmentConfirmResponse/Response/Error/ErrorDescription/");
            //get the error code
            $errorCode = $result->getValueForXpath("//ShipmentConfirmResponse/Response/Error/ErrorCode/");

            //add an error object to the result
            $result->setError($errorTitle, $errorCode);
            return $result;
        }

        return $result;
    }

    /**
     * Creates and submits the ShipmentAcceptRequest to approve the request to create a shipment.
     *
     * @param string $url The URL to submit the XML to.
     * @param Mage_Sales_Model_Order $order The shipping order
     * @param string $shipmentdigest The UPS generated digest to identify the request
     * @return unknown
     */
    protected function _sendXmlShipmentAcceptRequest($url, $order, $shipmentdigest) {
        if (empty($url)) {
            $url = rtrim($this->getConfig('shipping_xml_url'), '/').'/ShipAccept';
        }

        //start with the access request
        $xmlRequest = $this->_xmlAccessRequest;
        //add the rest of the XML
        $xmlRequest .= <<< XMLRequest

<?xml version="1.0"?>
<ShipmentAcceptRequest>
    <Request>
         <TransactionReference>
              <CustomerContext>{$order->getRealOrderId()}</CustomerContext>
              <XpciVersion>1.0001</XpciVersion>
         </TransactionReference>
         <RequestAction>ShipAccept</RequestAction>
    </Request>
    <ShipmentDigest><![CDATA[{$shipmentdigest}]]></ShipmentDigest>
</ShipmentAcceptRequest>
XMLRequest;

        $debugData = array('request' => $xmlRequest);
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec ($ch);

            $debugData['result'] = $xmlResponse;
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }
        $this->_debug($debugData);

        return $this->_parseXmlShipmentAcceptResponse($xmlResponse, $order->getRealOrderId());
    }

    /**
     * Parses the XML from a ShipmentAcceptReponse document and returns the results
     *
     * @param string $xmlResponse The full ShipmentConfirmResponse XML document
     * @param string $transaction_reference The reference id supplied in the initial ShipmentConfirmRequest.
     * @return Unl_Ship_Model_Shipment_Confirmation The ShipmentAcceptResponse object
     */
    protected function _parseXmlShipmentAcceptResponse($xmlResponse, $transaction_reference) {
        //create the result and set the raw response
        $result = Mage::getModel('unl_ship/shipment_confirmation');
        if (!$result->setRawResponse($xmlResponse)) {
            return $result;
        }

        $success = (int)$result->getValueForXpath("//ShipmentAcceptResponse/Response/ResponseStatusCode/");
        if ($success === 1) {
            //get the transaction reference and make sure it matches what was sent
            $tref = $result->getValueForXpath("//ShipmentAcceptResponse/Response/TransactionReference/CustomerContext/");
            //if the transaction reference does not match
            if ($tref != $transaction_reference) {
                //set error and throw exception
                $errormsg = "Transaction reference '".$tref."' received in response does not match transaction reference '".$transaction_reference.
                    "' that was sent in request.";
                $result->setError($errormsg);
                return $result;
            }

            //get the currency units
            $cunits = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/ShipmentCharges/TotalCharges/CurrencyCode/");
            $result->setCurrencyUnits($cunits);

            //get the shipping charges
            //transportation
            $trans = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/ShipmentCharges/TransportationCharges/MonetaryValue/");
            $result->setTransportationShippingCharges($trans);
            //service options
            $servopt = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/ShipmentCharges/ServiceOptionsCharges/MonetaryValue/");
            $result->setServiceOptionsShippingCharges($servopt);
            //total charges
            $total = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/ShipmentCharges/TotalCharges/MonetaryValue/");
            $result->setTotalShippingCharges($total);

            //get negotiated rate total
            $negtotal = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/NegotiatedRates/NetSummaryCharges/GrandTotal/MonetaryValue/");
            $result->setNegotiatedTotalShippingCharges($negtotal);

            //get billing weight
            //units
            $wunits = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/BillingWeight/UnitOfMeasurement/Code/");
            $result->setBillingWeightUnits($wunits);
            //weight
            $weight = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/BillingWeight/Weight/");
            $result->setBillingWeight($weight);

            //get the shipment id number
            $shipmentid = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/ShipmentIdentificationNumber/");
            $result->setShipmentIdentificationNumber($shipmentid);

            //high value report
            $ins_doc = $result->getValueForXpath('//ShipmentAcceptResponse/ShipmentResults/ControlLogReceipt/GraphicImage');
            $result->setInsDoc($ins_doc);

            //internation forms
            $intl_doc = $result->getValueForXpath('//ShipmentAcceptResponse/ShipmentResults/Form/Image/GraphicImage');
            $result->setIntlDoc($intl_doc);

            //get all packages
            $xmlpackages = $result->getXpath("//ShipmentAcceptResponse/ShipmentResults/PackageResults");
            $packages = array();
            if ($xmlpackages) {
                foreach ($xmlpackages as $package) {
                    //get the package info
                    //tracking number
                    $tracking = (string)$package->TrackingNumber;
                    //service option charges
                    $service_option_currency = null;
                    if (isset($package->ServiceOptionCharges->CurrencyCode)) {
                        $service_option_currency = (string)$tracking->ServiceOptionCharges->CurrencyCode;
                    }
                    $service_option_charge = null;
                    if (isset($package->ServiceOptionCharges->MonetaryValue)) {
                        $service_option_charge = (string)$tracking->ServiceOptionCharges->MonetaryValue;
                    }
                    //label images
                    $label_image = null;
                    $label_image_format = null;
                    $html_image = null;
                    if (isset($package->LabelImage)) {
                        $label_image_format = (string)$package->LabelImage->LabelImageFormat->Code;
                        $label_image = (string)$package->LabelImage->GraphicImage;  //Base64 encoded
                        if (isset($package->LabelImage->HTMLImage)) {
                            $html_image = (string)$package->LabelImage->HTMLImage;  //Base64 encoded GIF
                        }
                    }

                    //add data to packages array
                    $pkg = array(
                        'tracking_number' => $tracking,
                        'service_option_currency' => $service_option_currency,
                        'service_option_charge' => $service_option_charge,
                        'label_image_format' => $label_image_format,
                        'label_image' => $label_image,
                        'html_image' => $html_image,
                        'ins_doc' => $ins_doc,
                        'intl_doc' => $intl_doc
                    );
                    $packages[] = $pkg;
                }
            }
            $result->setPackages($packages);

            //make sure all required params are present
            if (empty($total) || empty($shipmentid) || empty($packages)) {
                $errmsg = "Required parameter(s) not found in response: ";
                if (empty($total)) {
                    $errmsg .= 'TotalCharges, ';
                }
                if (empty($shipmentid)) {
                    $errmsg .= 'ShipmentIdentificationNumber, ';
                }
                if (empty($packages)) {
                    $errmsg .= 'PackageResults';
                }
                $errmsg = rtrim($errmsg, ', ');
                $result->setError($errmsg);
                return $result;
            }
        }
        else  {
            $errortitle = $result->getValueForXpath("//ShipmentAcceptResponse/Response/Error/ErrorDescription/");
            $errorcode = $result->getValueForXpath("//ShipmentAcceptResponse/Response/Error/ErrorCode/");
            $result->setError($errortitle, $errorcode);
            return $result;
        }

        return $result;
    }
}
