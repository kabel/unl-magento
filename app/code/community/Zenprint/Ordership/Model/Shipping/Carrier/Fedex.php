<?php
/**
 * Zenprint
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zenprint.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2009 ZenPrint (http://www.zenprint.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Zenprint_Ordership_Model_Shipping_Carrier_Fedex
    extends Mage_Usa_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface, Zenprint_Ordership_Model_Shipping_Carrier_Xmlship_Interface
{

    protected $_code = 'fedex';

    protected $_request = null;
    
    protected $_shiprequest = null;

    protected $_result = null;

    protected $_gatewayUrl = 'https://gateway.fedex.com/GatewayDC';

	/**
     * Retrieves the dimension units for this carrier and store
     *
     * @return string IN or CM
     */
    public function getDimensionUnits()  {
    	return Mage::getStoreConfig('carriers/fedex/dimension_units', $this->getStore());
    }
    
	/**
     * Retrieves the weight units for this carrier and store
     *
     * @return string LBS or KGS
     */
    public function getWeightUnits()  {
    	return Mage::getStoreConfig('carriers/fedex/unit_of_measure', $this->getStore());
    }
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $this->setRequest($request);

        $this->_result = $this->_getQuotes();

        $this->_updateFreeMethodQuote($request);

        return $this->getResult();
    }

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setService($request->getLimitMethod());
        }

        //TODO: Add 3rd party quote account id here
        if ($request->getFedexAccount()) {
            $account = $request->getFedexAccount();
        } else {
            $account = $this->getConfigData('account');
        }

        $r->setAccount($account);

        if ($request->getFedexDropoff()) {
            $dropoff = $request->getFedexDropoff();
        } else {
            $dropoff = $this->getConfigData('dropoff');
        }
        $r->setDropoffType($dropoff);

        if ($request->getFedexPackaging()) {
            $packaging = $request->getFedexPackaging();
        } else {
            $packaging = $this->getConfigData('packaging');
        }
        $r->setPackaging($packaging);

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        }
        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        }

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeight($weight);
        if ($request->getFreeMethodWeight()!= $request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackageValue());

        $this->_rawRequest = $r;

        return $this;
    }

    public function getResult()
    {
       return $this->_result;
    }

    protected function _getQuotes()
    {
        return $this->_getXmlQuotes();
    }

    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;
        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeight($weight);
        $r->setService($freeMethod);
    }

    protected function _getXmlQuotes()
    {
        $r = $this->_rawRequest;

        $xml = new SimpleXMLElement('<FDXRateAvailableServicesRequest/>');

        $xml->addAttribute('xmlns:api', 'http://www.fedex.com/fsmapi');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXRateAvailableServicesRequest.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
//          $requestHeader->addChild('CustomerTransactionIdentifier', 'CTIString');
            $requestHeader->addChild('AccountNumber', $r->getAccount());
//          $requestHeader->addChild('MeterNumber', '2436351');  -- my own meter number
            $requestHeader->addChild('MeterNumber', '0');
//          $requestHeader->addChild('CarrierCode', 'FDXE');
//          $requestHeader->addChild('CarrierCode', 'FDXG');
            /**
             *  FDXE � FedEx Express
             *  FDXG � FedEx Ground
             */

        $xml->addChild('ShipDate', date('Y-m-d'));
//      $xml->addChild('ReturnShipmentIndicator', 'NONRETURN');
        /**
         *  � NONRETURN
         *  � PRINTRETURNLABEL
         *  � EMAILLABEL
         */
        $xml->addChild('DropoffType', $r->getDropoffType());
        /**
         *  � REGULARPICKUP
         *  � REQUESTCOURIER
         *  � DROPBOX
         *  � BUSINESSSERVICECENTER
         *  � STATION
         *  Only REGULARPICKUP, REQUESTCOURIER, and STATION are
         *  allowed with international freight shipping.
         */
        if ($r->hasService()) {
            $xml->addChild('Service', $r->getService());
        }
        /**
         *  One of the following FedEx Services is optional:
         *  � PRIORITYOVERNIGHT
         *  � STANDARDOVERNIGHT
         *  � FIRSTOVERNIGHT
         *  � FEDEX2DAY
         *  � FEDEXEXPRESSSAVER
         *  � INTERNATIONALPRIORITY
         *  � INTERNATIONALECONOMY
         *  � INTERNATIONALFIRST
         *  � FEDEX1DAYFREIGHT
         *  � FEDEX2DAYFREIGHT
         *  � FEDEX3DAYFREIGHT
         *  � FEDEXGROUND
         *  � GROUNDHOMEDELIVERY
         *  � INTERNATIONALPRIORITY FREIGHT
         *  � INTERNATIONALECONOMY FREIGHT
         *  � EUROPEFIRSTINTERNATIONALPRIORITY
         *  If provided, only that service�s estimated charges will be returned.
         */
        $xml->addChild('Packaging', $r->getPackaging());
        /**
         *  One of the following package types is required:
         *  � FEDEXENVELOPE
         *  � FEDEXPAK
         *  � FEDEXBOX
         *  � FEDEXTUBE
         *  � FEDEX10KGBOX
         *  � FEDEX25KGBOX
         *  � YOURPACKAGING
         *  If value entered is FEDEXENVELOPE, FEDEX10KGBOX, or
         *  FEDEX25KGBOX, an MPS rate quote is not allowed.
         */
        $xml->addChild('WeightUnits', 'LBS');
        /**
         *  � LBS
         *  � KGS
         *  LBS is required for a U.S. FedEx Express rate quote.
         */
        $xml->addChild('Weight', $r->getWeight());
//      $xml->addChild('ListRate', 'true');
        /**
         *  Optional.
         *  If = true or 1, list-rate courtesy quotes should be returned in addition to
         *  the discounted quote.
         */

        $originAddress = $xml->addChild('OriginAddress');
//          $originAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
            $originAddress->addChild('PostalCode', $r->getOrigPostal());
            $originAddress->addChild('CountryCode', $r->getOrigCountry());

        $destinationAddress = $xml->addChild('DestinationAddress');
//          $destinationAddress->addChild('StateOrProvinceCode', 'GA');   -- ???
            $destinationAddress->addChild('PostalCode', $r->getDestPostal());
            $destinationAddress->addChild('CountryCode', $r->getDestCountry());

        $payment = $xml->addChild('Payment');
            $payment->addChild('PayorType', 'SENDER');
            /**
             *  Optional.
             *  Defaults to SENDER.
             *  If value other than SENDER is used, no rates will still be returned.
             */

        /**
         *  DIMENSIONS
         *
         *  Dimensions / Length
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The length of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Width
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The width of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Height
         *  Optional.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The height of a package.
         *  Format: Numeric, whole number
         *
         *  Dimensions / Units
         *  Required if dimensions are entered.
         *  Only applicable if the package type is YOURPACKAGING.
         *  The valid unit of measure codes for the package dimensions are:
         *  IN � Inches
         *  CM � Centimeters
         *  U.S. FedEx Express must be in inches.
         */

        $declaredValue = $xml->addChild('DeclaredValue');
            $declaredValue->addChild('Value', $r->getValue());
//            $declaredValue->addChild('CurrencyCode', 'USD');
		$declaredValue->addChild('CurrencyCode', Mage::app()->getBaseCurrencyCode());

        if ($this->getConfigData('residence_delivery')) {
            $specialServices = $xml->addChild('SpecialServices');
                 $specialServices->addChild('ResidentialDelivery', 'true');
        }

//      $specialServices = $xml->addChild('SpecialServices');
//          $specialServices->addChild('Alcohol', 'true');
//          $specialServices->addChild('DangerousGoods', 'true')->addChild('Accessibility', 'ACCESSIBLE');
        /**
         *  Valid values:
         *  ACCESSIBLE � accessible DG
         *  INACCESSIBLE � inaccessible DG
         */
//          $specialServices->addChild('DryIce', 'true');
//          $specialServices->addChild('ResidentialDelivery', 'true');
        /**
         *  If = true or 1, the shipment is Residential Delivery. If Recipient Address
         *  is in a rural area (defined by table lookup), additional charge will be
         *  applied. This element is not applicable to the FedEx Home Delivery
         *  service.
         */
//          $specialServices->addChild('InsidePickup', 'true');
//          $specialServices->addChild('InsideDelivery', 'true');
//          $specialServices->addChild('SaturdayPickup', 'true');
//          $specialServices->addChild('SaturdayDelivery', 'true');
//          $specialServices->addChild('NonstandardContainer', 'true');
//          $specialServices->addChild('SignatureOption', 'true');
        /**
         *  Optional.
         *  Specifies the Delivery Signature Option requested for the shipment.
         *  Valid values:
         *  � DELIVERWITHOUTSIGNATURE
         *  � INDIRECT
         *  � DIRECT
         *  � ADULT
         *  For FedEx Express shipments, the DELIVERWITHOUTSIGNATURE
         *  option will not be allowed when the following special services are
         *  requested:
         *  � Alcohol
         *  � Hold at Location
         *  � Dangerous Goods
         *  � Declared Value greater than $500
         */

        /**
         *  HOMEDELIVERY
         *
         *  HomeDelivery / Type
         *  One of the following values are required for FedEx Home Delivery
         *  shipments:
         *  � DATECERTAIN
         *  � EVENING
         *  � APPOINTMENT
         *
         *  PackageCount
         *  Required for multiple-piece shipments (MPS).
         *  For MPS shipments, 1 piece = 1 box.
         *  For international Freightcurl MPS shipments, this is the total number of
         *  "units." Units are the skids, pallets, or boxes that make up a freight
         *  shipment.
         *  Each unit within a shipment should have its own label.
         *  FDXE only applies to COD, MPS, and international.
         *  Valid values: 1 to 999
         */

        /**
         *  VARIABLEHANDLINGCHARGES
         *
         *  VariableHandlingCharges / Level
         *  Optional.
         *  Only applicable if valid Variable Handling Type is present.
         *  Apply fixed or variable handling charges at package or shipment level.
         *  Valid values:
         *  � PACKAGE
         *  � SHIPMENT
         *  The value "SHIPMENT" is applicable only on last piece of FedEx
         *  Ground or FedEx Express MPS shipment only.
         *  Note: Value "SHIPMENT" = shipment level affects the entire shipment.
         *  Anything else sent in Child will be ignored.
         *
         *  VariableHandlingCharges / Type
         *  Optional.
         *  If valid value is present, a valid Variable Handling Charge is required.
         *  Specifies what type of Variable Handling charges to assess and on
         *  which amount.
         *  Valid values:
         *  � FIXED_AMOUNT
         *  � PERCENTAGE_OF_BASE
         *  � PERCENTAGE_OF_NET
         *  � PERCENTAGE_OF_NET_ EXCL_TAXES
         *
         *  VariableHandlingCharges / AmountOrPercentage
         *  Optional.
         *  Required in conjunction with Variable Handling Type.
         *  Contains the dollar or percentage amount to be added to the Freight
         *  charges. Whether the amount is a dollar or percentage is based on the
         *  Variable Handling Type value that is included in this Request.
         *  Format: Two explicit decimal positions (e.g. 1.00); 10 total length
         *  including decimal place.
         */

        $xml->addChild('PackageCount', '1');

        $request = $xml->asXML();
/*
        $client = new Zend_Http_Client();
        $client->setUri($this->getConfigData('gateway_url'));
        $client->setConfig(array('maxredirects'=>0, 'timeout'=>30));
        $client->setParameterPost($request);
        $response = $client->request();
        $responseBody = $response->getBody();
*/


		$url = $this->getConfigData('gateway_url');
		//set to the live URL for quotes if in test mode
		if (!$url || $this->getConfigData('test_mode')) {
			$url = $this->_gatewayUrl;
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		//FIXME: Without the next 2 options, SSL calls will fail in some OSes where default CA file does not
		//	exist in /etc/ssl/certs/ca-certificates.crt (Curl default). This is a security risk
		//	since the SSL cert will not be verified
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
		$responseBody = curl_exec($ch);

		//check for error
		if(curl_error($ch))  {
			throw new Exception(Mage::helper('ordership')->__('Error connecting to API:  ').curl_error($ch));
		}
		curl_close($ch);
        
        return $this->_parseXmlResponse($responseBody);
    }

    protected function _parseXmlResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = 'Unable to retrieve quotes';
        if (strlen(trim($response))>0) {
            if (strpos(trim($response), '<?xml')===0) {
                $xml = simplexml_load_string($response);
                if (is_object($xml)) {
                    if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                        $errorTitle = (string)$xml->Error->Message;
                    } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                        $errorTitle = (string)$xml->SoftError->Message;
                    } else {
                        $errorTitle = 'Unknown error';
                    }
                    $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
                    foreach ($xml->Entry as $entry) {
                        if (in_array((string)$entry->Service, $allowedMethods)) {
                            $costArr[(string)$entry->Service] = (string)$entry->EstimatedCharges->DiscountedCharges->NetCharge;
                            $priceArr[(string)$entry->Service] = $this->getMethodPrice((string)$entry->EstimatedCharges->DiscountedCharges->NetCharge, (string)$entry->Service);
                        }
                    }
                    asort($priceArr);
                }
            } else {
                $errorTitle = 'Response is in the wrong format';
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
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

/*
    public function isEligibleForFree($method)
    {
        return $method=='FEDEXGROUND';
    }
*/
    
    /**
     * Converts from code to code with underscores. Needed because XML does not use _ in codes, while SOAP interface does.
     *
     * @param string $code
     * @return string Code with underscore.
     */
    public function getUnderscoreCodeFromCode($code)  {
    	$codes = array(
    		//method
    		'PRIORITYOVERNIGHT' => 'PRIORITY_OVERNIGHT',
			'STANDARDOVERNIGHT' => 'STANDARD_OVERNIGHT',
			'FIRSTOVERNIGHT' => 'FIRST_OVERNIGHT',
			'FEDEX2DAY' => 'FEDEX_2_DAY',
			'FEDEXEXPRESSSAVER' => 'FEDEX_EXPRESS_SAVER',
			'INTERNATIONALPRIORITY' => 'INTERNATIONAL_PRIORITY',
			'INTERNATIONALECONOMY' => 'INTERNATIONAL_ECONOMY',
			'INTERNATIONALFIRST' => 'INTERNATIONAL_FIRST',
			'FEDEX1DAYFREIGHT' => 'FEDEX_1DAY_FREIGHT',
			'FEDEX2DAYFREIGHT' => 'FEDEX_2DAY_FREIGHT',
			'FEDEX3DAYFREIGHT' => 'FEDEX_3DAY_FREIGHT',
			'FEDEXGROUND' => 'FEDEX_GROUND',
			'GROUNDHOMEDELIVERY' => 'GROUND_HOME_DELIVERY',
			'INTERNATIONALPRIORITY FREIGHT' => 'INTERNATIONAL_PRIORITY_FREIGHT',
			'INTERNATIONALECONOMY FREIGHT' => 'INTERNATIONAL_ECONOMY_FREIGHT',
			'EUROPEFIRSTINTERNATIONALPRIORITY' => 'EUROPE_FIRST_INTERNATIONAL_PRIORITY',
    		//dropoff
    		'REGULARPICKUP' => 'REGULAR_PICKUP',
			'REQUESTCOURIER' => 'REQUEST_COURIER',
			'DROPBOX' => 'DROP_BOX',
			'BUSINESSSERVICECENTER' => 'BUSINESS_SERVICE_CENTER',
			'STATION' => 'STATION',
    		//packaging
    		'FEDEXENVELOPE' => 'FEDEX_ENVELOPE',
			'FEDEXPAK' => 'FEDEX_PAK',
			'FEDEXBOX' => 'FEDEX_BOX',
			'FEDEXTUBE' => 'FEDEX_TUBE',
			'FEDEX10KGBOX' => 'FEDEX_10KG_BOX',
			'FEDEX25KGBOX' => 'FEDEX_25KG_BOX',
			'YOURPACKAGING' => 'YOUR_PACKAGING',
    	);
    	
    	return $codes[$code];
    }

    public function getCode($type, $code='', $underscore=false)
    {
    	//get the customer defined package dimensions in in and cm (rounded to 2 dec places)
    	if($this->getConfigData('dimension_units') == 'IN')  {
    		$cdef_height_in = round($this->getConfigData('default_height'), 2);
    		$cdef_width_in = round($this->getConfigData('default_width'), 2);
    		$cdef_length_in = round($this->getConfigData('default_length'), 2);
    		$cdef_height_cm = round($this->getConfigData('default_height') * 2.54, 2);
    		$cdef_width_cm = round($this->getConfigData('default_width') * 2.54, 2);
    		$cdef_length_cm = round($this->getConfigData('default_length') * 2.54, 2);
    	}
    	else  {
    		$cdef_height_in = round($this->getConfigData('default_height') * 0.393700787, 2);
    		$cdef_width_in = round($this->getConfigData('default_width') * 0.393700787, 2);
    		$cdef_length_in = round($this->getConfigData('default_length') * 0.393700787, 2);
    		$cdef_height_cm = round($this->getConfigData('default_height'), 2);
    		$cdef_width_cm = round($this->getConfigData('default_width'), 2);
    		$cdef_length_cm = round($this->getConfigData('default_length'), 2);
    	}
    	
        //Needed since XML requests have no underscore, while SOAP interface includes them (nice one Fedex)
        $codes_underscore = array(
            'method'=>array(
                'PRIORITY_OVERNIGHT'                => Mage::helper('usa')->__('Priority Overnight'),
                'STANDARD_OVERNIGHT'                => Mage::helper('usa')->__('Standard Overnight'),
                'FIRST_OVERNIGHT'                   => Mage::helper('usa')->__('First Overnight'),
                'FEDEX_2DAY'                        => Mage::helper('usa')->__('2Day'),
                'FEDEX_EXPRESS_SAVER'                => Mage::helper('usa')->__('Express Saver'),
                'INTERNATIONAL_PRIORITY'            => Mage::helper('usa')->__('International Priority'),
                'INTERNATIONAL_ECONOMY'             => Mage::helper('usa')->__('International Economy'),
                'INTERNATIONAL_FIRST'               => Mage::helper('usa')->__('International First'),
                'FEDEX_1DAY_FREIGHT'                 => Mage::helper('usa')->__('1 Day Freight'),
                'FEDEX_2DAY_FREIGHT'                 => Mage::helper('usa')->__('2 Day Freight'),
                'FEDEX_3DAY_FREIGHT'                 => Mage::helper('usa')->__('3 Day Freight'),
                'FEDEX_GROUND'                      => Mage::helper('usa')->__('Ground'),
                'GROUND_HOME_DELIVERY'               => Mage::helper('usa')->__('Home Delivery'),
                'INTERNATIONAL_PRIORITY_FREIGHT'    => Mage::helper('usa')->__('Intl Priority Freight'),
                'INTERNATIONAL_ECONOMY_FREIGHT'     => Mage::helper('usa')->__('Intl Economy Freight'),
                'EUROPE_FIRST_INTERNATIONAL_PRIORITY' => Mage::helper('usa')->__('Europe First Priority'),
            ),

            'dropoff'=>array(
                'REGULAR_PICKUP'         => Mage::helper('usa')->__('Regular Pickup'),
                'REQUEST_COURIER'        => Mage::helper('usa')->__('Request Courier'),
                'DROP_BOX'               => Mage::helper('usa')->__('Drop Box'),
                'BUSINESS_SERVICE_CENTER' => Mage::helper('usa')->__('Business Service Center'),
                'STATION'               => Mage::helper('usa')->__('Station'),
            ),

            'packaging'=>array(
                'FEDEX_ENVELOPE' => Mage::helper('usa')->__('FedEx Envelope'),
                'FEDEX_PAK'      => Mage::helper('usa')->__('FedEx Pak'),
                'FEDEX_BOX'      => Mage::helper('usa')->__('FedEx Box'),
                'FEDEX_TUBE'     => Mage::helper('usa')->__('FedEx Tube'),
                'FEDEX_10KG_BOX'  => Mage::helper('usa')->__('FedEx 10kg Box'),
                'FEDEX_25KG_BOX'  => Mage::helper('usa')->__('FedEx 25kg Box'),
                'YOUR_PACKAGING' => Mage::helper('usa')->__('Your Packaging'),
            ),
        );
        
        $codes = array(
            'method'=>array(
                'PRIORITYOVERNIGHT'                => Mage::helper('usa')->__('Priority Overnight'),
                'STANDARDOVERNIGHT'                => Mage::helper('usa')->__('Standard Overnight'),
                'FIRSTOVERNIGHT'                   => Mage::helper('usa')->__('First Overnight'),
                'FEDEX2DAY'                        => Mage::helper('usa')->__('2Day'),
                'FEDEXEXPRESSSAVER'                => Mage::helper('usa')->__('Express Saver'),
                'INTERNATIONALPRIORITY'            => Mage::helper('usa')->__('International Priority'),
                'INTERNATIONALECONOMY'             => Mage::helper('usa')->__('International Economy'),
                'INTERNATIONALFIRST'               => Mage::helper('usa')->__('International First'),
                'FEDEX1DAYFREIGHT'                 => Mage::helper('usa')->__('1 Day Freight'),
                'FEDEX2DAYFREIGHT'                 => Mage::helper('usa')->__('2 Day Freight'),
                'FEDEX3DAYFREIGHT'                 => Mage::helper('usa')->__('3 Day Freight'),
                'FEDEXGROUND'                      => Mage::helper('usa')->__('Ground'),
                'GROUNDHOMEDELIVERY'               => Mage::helper('usa')->__('Home Delivery'),
                'INTERNATIONALPRIORITY FREIGHT'    => Mage::helper('usa')->__('Intl Priority Freight'),
                'INTERNATIONALECONOMY FREIGHT'     => Mage::helper('usa')->__('Intl Economy Freight'),
                'EUROPEFIRSTINTERNATIONALPRIORITY' => Mage::helper('usa')->__('Europe First Priority'),
            ),

            'dropoff'=>array(
                'REGULARPICKUP'         => Mage::helper('usa')->__('Regular Pickup'),
                'REQUESTCOURIER'        => Mage::helper('usa')->__('Request Courier'),
                'DROPBOX'               => Mage::helper('usa')->__('Drop Box'),
                'BUSINESSSERVICE_CENTER' => Mage::helper('usa')->__('Business Service Center'),
                'STATION'               => Mage::helper('usa')->__('Station'),
            ),

            'packaging'=>array(
                'FEDEXENVELOPE' => Mage::helper('usa')->__('FedEx Envelope'),
                'FEDEXPAK'      => Mage::helper('usa')->__('FedEx Pak'),
                'FEDEXBOX'      => Mage::helper('usa')->__('FedEx Box'),
                'FEDEXTUBE'     => Mage::helper('usa')->__('FedEx Tube'),
                'FEDEX10KGBOX'  => Mage::helper('usa')->__('FedEx 10kg Box'),
                'FEDEX25KGBOX'  => Mage::helper('usa')->__('FedEx 25kg Box'),
                'YOURPACKAGING' => Mage::helper('usa')->__('Your Packaging'),
            ),

            'unit_of_dimension'=>array(
                'IN' => Mage::helper('usa')->__('Inches'),
                'CM' => Mage::helper('usa')->__('Centimeters'),
            ),
            
            //these are dimensions in centimeters for each package type
            'package_dimensions_cm' => array(
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
             	'YOUR_PACKAGING' => array(
            		'height' => $cdef_height_cm,
            		'width' => $cdef_width_cm,
            		'length' => $cdef_length_cm,	
            	),
            ),
            
            //these are dimensions in inches for each package type
            'package_dimensions_in' => array(
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
            	'FEDEX_10KG_BOX' => array(
            		'height' => 15.81,
            		'width' => 12.94,
            		'length' => 40.16,	
            	),
             	'FEDEX_25KG_BOX' => array(
            		'height' => 13.19,
            		'width' => 16.56,
            		'length' => 21.56,	
            	),
            	'YOUR_PACKAGING' => array(
            		'height' => $cdef_height_in,
            		'width' => $cdef_width_in,
            		'length' => $cdef_length_in,	
            	),
            ),
        );
		
        if($underscore)  {
        	if (!isset($codes_underscore[$type])) {
	//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code type: %s', $type));
	            return false;
	        } elseif (''===$code) {
	            return $codes_underscore[$type];
	        }
	
	        if (!isset($codes_underscore[$type][$code])) {
	//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code for type %s: %s', $type, $code));
	            return false;
	        } else {
	            return $codes_underscore[$type][$code];
	        }
        }
        else  {
	        if (!isset($codes[$type])) {
	//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code type: %s', $type));
	            return false;
	        } elseif (''===$code) {
	            return $codes[$type];
	        }
	
	        if (!isset($codes[$type][$code])) {
	//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid FedEx XML code for type %s: %s', $type, $code));
	            return false;
	        } else {
	            return $codes[$type][$code];
	        }
        }
    }

    public function getTracking($trackings)
    {
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings=array($trackings);
        }

        foreach($trackings as $tracking){
            $this->_getXMLTracking($tracking);
        }

        return $this->_result;
    }

    protected function setTrackingReqeust()
    {
        $r = new Varien_Object();

        $account = $this->getConfigData('account');
        $r->setAccount($account);

        $this->_rawTrackingRequest = $r;

    }
    protected function _getXMLTracking($tracking)
    {
        $r = $this->_rawTrackingRequest;

        $xml = new SimpleXMLElement('<FDXTrack2Request/>');
        $xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $xml->addAttribute('xsi:noNamespaceSchemaLocation', 'FDXTrack2Request.xsd');

        $requestHeader = $xml->addChild('RequestHeader');
        $requestHeader->addChild('AccountNumber', $r->getAccount());

        /*
        * for tracking result, actual meter number is not needed
        */
        $requestHeader->addChild('MeterNumber', '0');

        $packageIdentifier = $xml->addChild('PackageIdentifier');
        $packageIdentifier->addChild('Value', $tracking);

        /*
        * 0 = summary data, one signle scan structure with the most recent scan
        * 1 = multiple sacn activity for each package
        */
        $xml->addChild('DetailScans', '1');

        $request = $xml->asXML();
//        try {
            $url = $this->getConfigData('gateway_url');
            if (!$url) {
                $url = $this->_gatewayUrl;
            }
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            //FIXME: Without the next 2 options, SSL calls will fail in some OSes where default CA file does not
			//	exist in /etc/ssl/certs/ca-certificates.crt (Curl default). This is a security risk
			//	since the SSL cert will not be verified
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            $responseBody = curl_exec($ch);
            curl_close ($ch);
//        } catch (Exception $e) {
//            $responseBody = '';
//        }
		//check for error
		if(curl_error($ch))  {
			throw new Exception(Mage::helper('ordership')->__('Error connecting to API:  ').curl_error($ch));
		}
		curl_close($ch);

        #echo "<xmp>".$responseBody."</xmp>";
        $this->_parseXmlTrackingResponse($tracking, $responseBody);
    }

    protected function _parseXmlTrackingResponse($trackingvalue,$response)
    {
         $resultArr=array();
         if (strlen(trim($response))>0) {
              if (strpos(trim($response), '<?xml')===0) {
                  $xml = simplexml_load_string($response);
                  if (is_object($xml)) {
                    if (is_object($xml->Error) && is_object($xml->Error->Message)) {
                        $errorTitle = (string)$xml->Error->Message;
                    } elseif (is_object($xml->SoftError) && is_object($xml->SoftError->Message)) {
                        $errorTitle = (string)$xml->SoftError->Message;
                    }
                  }else{
                      $errorTitle = 'Error in loading response';
                  }

                  if (!isset($errorTitle)) {
                      $resultArr['status'] = (string)$xml->Package->StatusDescription;
                      $resultArr['service'] = (string)$xml->Package->Service;
                      $resultArr['deliverydate'] = (string)$xml->Package->DeliveredDate;
                      $resultArr['deliverytime'] = (string)$xml->Package->DeliveredTime;
                      $resultArr['deliverylocation'] = (string)$xml->TrackProfile->DeliveredLocationDescription;
                      $resultArr['signedby'] = (string)$xml->Package->SignedForBy;
                      $resultArr['shippeddate'] = (string)$xml->Package->ShipDate;
                      $weight = (string)$xml->Package->Weight;
                      $unit = (string)$xml->Package->WeightUnits;
                      $resultArr['weight'] = "{$weight} {$unit}";

                      $packageProgress = array();
                      if (isset($xml->Package->Event)) {
                          foreach ($xml->Package->Event as $event) {
                              $tempArr=array();
                              $tempArr['activity'] = (string)$event->Description;
                              $tempArr['deliverydate'] = (string)$event->Date;//YYYY-MM-DD
                              $tempArr['deliverytime'] = (string)$event->Time;//HH:MM:ss
                              $addArr=array();
                              if (isset($event->Address->City)) {
                                $addArr[] = (string)$event->Address->City;
                              }
                              if (isset($event->Address->StateProvinceCode)) {
                                $addArr[] = (string)$event->Address->StateProvinceCode;
                              }
                              if (isset($event->Address->CountryCode)) {
                                $addArr[] = (string)$event->Address->CountryCode;
                              }
                              if ($addArr) {
                                $tempArr['deliverylocation']=implode(', ',$addArr);
                              }
                              $packageProgress[] = $tempArr;
                          }
                      }

                      $resultArr['progressdetail'] = $packageProgress;
                }
              } else {
                $errorTitle = 'Response is in the wrong format';
              }
         }

         if(!$this->_result){
             $this->_result = Mage::getModel('shipping/tracking_result');
         }
         $defaults = $this->getDefaults();

         if($resultArr){
             $tracking = Mage::getModel('shipping/tracking_result_status');
             $tracking->setCarrier('fedex');
             $tracking->setCarrierTitle($this->getConfigData('title'));
             $tracking->setTracking($trackingvalue);
             $tracking->addData($resultArr);
             $this->_result->append($tracking);
         }else{
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($errorTitle ? $errorTitle : Mage::helper('usa')->__('Unable to retrieve tracking'));
            $this->_result->append($error);
         }
    }

    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (!empty($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status'])."\n<br/>";
                        } else {
                            $statuses .= Mage::helper('usa')->__('Empty response')."\n<br/>";
                        }
                    }
                }
            }
        }
        if (empty($statuses)) {
            $statuses = Mage::helper('usa')->__('Empty response');
        }
        return $statuses;
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
        foreach ($allowed as $k) {
            $arr[$k] = $this->getCode('method', $k);
        }
        return $arr;
    }
    
    /********************************************************************************************************************
     * New functions created for shipment functionality as defined by Mage_Usa_Model_Shipping_Carrier_Xmlship_Interface
     ********************************************************************************************************************/
	public function setShipRequest(Zenprint_Ordership_Model_Shipment_Request $request)  {
        $this->_shiprequest = $request;
        return $this;
    }
    
    /**
     * Submits a request for shipment to the Fedex API. It will use the settings for the store from which the specifed order originates.
     *
     * @param int $orderid 
     * @param  $package The details of what is to be shipped.
     * @return The result of the request.
     */
    protected function _sendShipmentRequest($orderid, $package)  {
    	//set wsdl cache (turn off for debugging purposes if needed)
    	ini_set('soap.wsdl_cache_enabled', 1);
    	
    	//get the order information
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
        if(empty($order) || $order->getEntityId() == null)  {
        	throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid order id.'));
        	return false;
        }
        $shipaddress = $order->getShippingAddress();
        $store = $order->getStore();
    	
    	//get the path to the WSDL
    	$wsdlpath = dirname(__FILE__).'/wsdl/ShipService_v5.wsdl';
  	
    	if($store->getConfig('carriers/fedex/test_mode'))  {
    		$wsdlpath = dirname(__FILE__).'/wsdl/testShipService_v5.wsdl';
    	}

    	$client = new SoapClient($wsdlpath);
		$request = array();
		
    	//get request variables
    	//payment type
    	if($store->getConfig('carriers/fedex/third_party') == 1)  {
    		$shipping_charges_payment = array('PaymentType' => 'THIRD_PARTY', 'Payor' => array('AccountNumber' => $store->getConfig('carriers/fedex/third_party_fedex_account'), 'CountryCode' => $store->getConfig('carriers/fedex/third_party_fedex_account_country')));
    	}
    	else  {
    		$shipping_charges_payment = array('PaymentType' => 'SENDER', 'Payor' => array('AccountNumber' => $store->getConfig('carriers/fedex/account'), 'CountryCode' => $store->getConfig('carriers/fedex/account_country')));
    	}
    	
    	//contents
    	$contents = array();
    	foreach ($package->getItems() as $itemid => $qty)  {
    		$item = Mage::getModel('sales/order_item')->load($itemid);
    		$contents[] = array(
    			'ItemNumber' => $itemid, 
    			'Description' => strip_tags($item->getDescription()), 
    			'ReceivedQuantity' => $qty,
    		); 
    	}
    	
    	//service type
    	$servicetype = explode('_', $order->getShippingMethod());
    	unset($servicetype[0]);
    	$servicetype = $this->getUnderscoreCodeFromCode(implode('_', $servicetype));

		//shipper streetlines
		$shipperstreetlines = array($store->getConfig('shipping/origin/address1'));
		if($store->getConfig('shipping/origin/address2') != '')  {
			$shipperstreetlines[] = $store->getConfig('shipping/origin/address2');
		}
		if($store->getConfig('shipping/origin/address3') != '')  {
			$shipperstreetlines[] = $store->getConfig('shipping/origin/address3');
		}
		
		//recipient streetlines
		$recipientstreetlines = array($shipaddress->getStreet(1));
		if($shipaddress->getStreet(2) != '')  {
			$recipientstreetlines[] = $shipaddress->getStreet(2);
		}
		if($shipaddress->getStreet(3) != '')  {
			$recipientstreetlines[] = $shipaddress->getStreet(3);
		}
    	
    	//generate request
    	$request['WebAuthenticationDetail'] = array('UserCredential' => array('Key' => $store->getConfig('carriers/fedex/key'), 'Password' => $store->getConfig('carriers/fedex/password')));
		$request['ClientDetail'] = array('AccountNumber' => $store->getConfig('carriers/fedex/account'), 'MeterNumber' => $store->getConfig('carriers/fedex/meter'));
		$request['TransactionDetail'] = array('CustomerTransactionId' => $orderid.'_pkg'.$package->getPackageNumber());
		$request['Version'] = array('ServiceId' => 'ship', 'Major' => 5, 'Intermediate' => 0, 'Minor' => 0);
		$request['RequestedShipment'] = array('ShipTimestamp' => date('c'),
			'DropoffType' => $this->getUnderscoreCodeFromCode($store->getConfig('carriers/fedex/dropoff')), // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => $servicetype,
			'PackagingType' => $this->getUnderscoreCodeFromCode($store->getConfig('carriers/fedex/packaging')), // valid values FEDEX_BOK, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
			'Shipper' => array('Contact' => array('CompanyName' => $order->getStore()->getWebsite()->getName(),
					'PhoneNumber' => $store->getConfig('shipping/origin/phone')),
				'Address' => array(
					'StreetLines' => $shipperstreetlines,
					'City' => $store->getConfig('shipping/origin/city'),
					'StateOrProvinceCode' => Mage::getModel('directory/region')->load($store->getConfig('shipping/origin/region_id'))->getCode(),
					'PostalCode' => $store->getConfig('shipping/origin/postcode'),
					'CountryCode' => $store->getConfig('shipping/origin/country_id'))),
			'Recipient' => array('Contact' => array('PersonName' => $shipaddress->getName(), 'PhoneNumber' => $shipaddress->getTelephone()),
				'Address' => array(
					'StreetLines' => $recipientstreetlines,
					'City' => $shipaddress->getCity(),
					'StateOrProvinceCode' => $shipaddress->getRegionCode(),
					'PostalCode' => $shipaddress->getPostcode(),
					'CountryCode' => $shipaddress->getCountryId())),
			'ShippingChargesPayment' => $shipping_charges_payment,
			'LabelSpecification' => array('LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
				'ImageType' => 'PNG', // valid values DPL, EPL2, PDF, ZPLII and PNG
				'LabelStockType' => 'PAPER_4X6',
		
				//TODO: Add return address functionality using PrintedLabelOrigin
				
				//Mask the account number on the label
				'CustomerSpecifiedLabelDetail' => array('MaskedData' => array('SHIPPER_ACCOUNT_NUMBER'))),
			'RateRequestTypes' => array('ACCOUNT'), // valid values ACCOUNT and LIST
			'PackageCount' => 1,
			'RequestedPackages' => array('Weight' => array('Value' => sprintf("%01.1f", round($package->getWeight(), 1)), 'Units' => substr($package->getWeightUnitCode(), 0, 2)), // valid values LB or KG
				'SequenceNumber' => 1,
				'CustomerReferences' => array('0' => array('CustomerReferenceType' => 'CUSTOMER_REFERENCE', 'Value' => $orderid.'_pkg'.$package->getPackageNumber())), // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
				'ContentRecords' => $contents)
		);
		
    	//dimensions
    	if($package->getDimensionUnitCode())  {
    		$request['RequestedShipment']['RequestedPackages']['Dimensions'] = array('Length' => $package->getLength(), 'Width' => $package->getWidth(), 'Height' => $package->getHeight(),
					'Units' => $package->getDimensionUnitCode()); // valid values IN or CM
    	}
    	
    	//international
    	if($store->getConfig('shipping/origin/country_id') != $shipaddress->getCountryId())  {
    		//determine item values and details
    		$itemtotal = 0;
    		$itemdetails = array();
    		foreach ($package->getItems() as $itemid => $qty)  {
    			$item = Mage::getModel('sales/order_item')->load($itemid);   					
    			$itemtotal += $item->getPrice();
    			$itemdetails[] = array(
    				'NumberOfPieces' => 1,
    				'Description' => $item->getName(),
    				'CountryOfManufacture' => $store->getConfig('shipping/origin/country_id'),
    				'Weight' => array('Value' => $item->getWeight(), 'Units' => substr($package->getWeightUnitCode(), 0, 2)),
    				'Quantity' => $qty,
    				'QuantityUnits' => 'EA',
    				'UnitPrice' => array('Amount' => sprintf('%01.2f', $item->getPrice()), 'Currency' => 'USD'),
    				'CustomsValue' => array('Amount' => sprintf('%01.2f', ($item->getPrice() * $qty)), 'Currency' => 'USD'),
    			);
    		}
    		//The next 3 lines are a hack needed to trick the PHP5 SOAP client into not creating an optimized reference in the SOAP request
    		//Without it, the client will create an id="ref1" attribute on the ShippingChargesPayment element since it is the same as the
    		//'DutiesPayment' element. Adding these 2 dummy values tricks the SoapClient into thinking they are unique, but the dummy values are ignored
    		//in the actual request sent. 
    		$shipping_charges_payment_int = $shipping_charges_payment;
    		$shipping_charges_payment_int['Dummy'] = '123';
    		$shipping_charges_payment_int['Payor']['Dummy2'] = '234';
    		$request['RequestedShipment']['InternationalDetail'] = array('DutiesPayment' => $shipping_charges_payment_int,
    			'DocumentContent' => 'NON_DOCUMENTS',
				'CustomsValue' => array('Amount' => sprintf('%01.2f', $itemtotal), 'Currency' => 'USD'),
				'Commodities' => $itemdetails);
    	}
    	
		$this->_request = $request;
		$this->_result = $client->processShipment($request);

		return $this->_result;
    }
    
    /**
     * Parses the shipment request response.
     *
     * @param $orderid The id of the order to parse a response for.
     * @param $response The response. If empty, the object's result will be used.
     * @return
     */
    protected function _parseShipmentResponse($orderid, $response=null)  {
    	if(empty($response))  {
    		$response = $this->_result;
    	}
    	
    	//make sure there is a result to process
    	if(empty($response))  {
    		throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('There was no API response to the shipment request for order id ').$orderid);
    	}
    	
    	//get the order and store
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
    	$store = $order->getStore();
    	
    	$newline = "\n";

    	//check for success
	    if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR')  {    		    	
	    	//create the result and set the raw response
	    	$result = Mage::getModel('shipping/shipment_confirmation');
	    	if(!$result->setRawResponse($response, false))  {
	    		return $result;
	    	}
	    	
			if($store->getConfig('carriers/fedex/third_party') != 1)  {	    
				if(!is_array($response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails))  {				
					//get the currency units
					$cunits = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetCharge->Currency;
					//total charges
					$total = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails->TotalNetCharge->Amount;
					
					//get the billing weight
					//units
					$wunits = $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->BillingWeight->Units;
					//weight
					$weight = $response->CompletedShipmentDetail->CompletedPackageDetails->PackageRating->PackageRateDetails->BillingWeight->Value;
				}
				//Get the PAYOR_ACCOUNT values
				else  {
					//get the currency units
					$cunits = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetCharge->Currency;			
					//total charges
					$total = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalNetCharge->Amount;

					//get billing weight
					//units
					$wunits = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalBillingWeight->Units;
					//weight
					$weight = $response->CompletedShipmentDetail->ShipmentRating->ShipmentRateDetails[0]->TotalBillingWeight->Value;
				}
	
				//set values
				$result->setCurrencyUnits($cunits);
				$result->setTotalShippingCharges($total);
				$result->setBillingWeightUnits($wunits);
				$result->setBillingWeight($weight);
			}
			
			//get the shipment id number
			$shipmentid = $response->TransactionDetail->CustomerTransactionId;
			$result->setShipmentIdentificationNumber($shipmentid);
			
			//get package data
			$packages = array();
			$packages[] = array(
                	'package_number' => 1,
                	'tracking_number' => $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingId->TrackingNumber,
                	'service_option_currency' => '',
                	'service_option_charge' => '',
                	'label_image_format' => 'png',
                	'label_image' => base64_encode($response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image),
					'html_image' => '',
				);
			$result->setPackages($packages);
			
			//make sure all required params are present
			if(empty($shipmentid) || empty($packages))  {
				$errmsg = "Required parameter(s) not found in response: ";
				if(empty($shipmentid))  {
					$errmsg .= 'CustomerTransactionId, ';			
				}
				if(empty($packages))  {
					$errmsg .= 'CompletedPackageDetails';		
				}
				$errmsg = rtrim($errmsg, ', ');
				$result->setError($errmsg);
				return $result; 
			}

			return $result;
	    }
	    //if there was an error
	    else  {
	        $msg = ''; 
		if(is_array($response->Notifications))  {
		        foreach ($response->Notifications as $notification)  {
		        	$msg .= $notification->Severity.': '.$notification->Message.$newline;
			}
		}
		else  {
			$msg .= $response->Notifications->Severity.': '.$response->Notifications->Message.$newline;
		}
	        
	        throw Mage::exception('Mage_Shipping', $msg);
	    }
    }
    
	/**
     * Creates a shipment to be sent. Initializes the shipment, retrieves tracking number, and creates shipping label.
     * 
     * @return array An array of Mage_Shipping_Model_Shipment_Package objects. An exception should be thrown on error.
     */
    public function createShipment(Zenprint_Ordership_Model_Shipment_Request $request)  {
    	$this->setShipRequest($request);
        $this->_result = $this->_createShipment();

        return $this->_result;
    }
    
    /**
     * Creates shipments through API based on request params submitted.
     *
     */
    protected function _createShipment()  {
    	//get data from request
    	$orderid = $this->_shiprequest->getOrderId();
    	$packages = $this->_shiprequest->getPackages();

    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
    	
    	//make sure Fedex is enabled for the order's store
    	if (!$order->getStore()->getConfig('carriers/fedex/active'))  {
    		throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Fedex shipping is currently disabled for this order\'s store.'));
    	}
    	
    	//make sure the order_item ids passed in with packages are correct
    	foreach ($packages as $p)  {
    		foreach ($p->getItems() as $id => $qty)  {
    			$oitem = Mage::getModel('sales/order_item')->load($id);
    			if($oitem->getOrder()->getIncrementId() != $orderid)  {
					throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid submission data. '.$oitem->getOrder()->getIncrementId().' '.$orderid));
				}
    		}
    	}
    	
    	$retval = array();

    	//only send one package per request so that we can track the price of individual packages
    	foreach ($packages as $reqpackage)  {
    		//shipment request
			$shipresponse = $this->_sendShipmentRequest($orderid, $reqpackage);
			$shipresult = $this->_parseShipmentResponse($orderid, $shipresponse);
			
			//store the results of the request if it was successful (there will be no exceptions thrown)
			foreach ($shipresult->getPackages() as $respackage)  {
				//create an order shipment
				$convertor = Mage::getModel('sales/convert_order');
            	$shipment = $convertor->toShipment($order);
            	$resitems = $reqpackage->getItems();

            	//add shipped items
            	foreach($resitems as $itemid => $itemqty)  {
            		$orderItem = $order->getItemById($itemid);
            		$item = $convertor->itemToShipmentItem($orderItem);
                	$item->setQty($itemqty);
                	$shipment->addItem($item);
            	}
            	
	            //add the tracking number
				$track = Mage::getModel('sales/order_shipment_track')
					->setCarrierCode('fedex')
					->setNumber($respackage['tracking_number'])
					->setShipment($shipment);
				$shipment->addTrack($track);
				
				//save the shipment
				$shipment->register();
				$transactionSave = Mage::getModel('core/resource_transaction')
		            ->addObject($shipment)
		            ->addObject($shipment->getOrder())
		            ->save();
				
				//send shipment email
				$shipment->sendEmail();
	    		
		    	//create a package to store
		    	$pkg = Mage::getModel('shipping/shipment_package')
		    		->setOrderIncrementId($orderid)
		    		->setOrderShipmentId($shipment->getId())
		    		->setCarrier('fedex')
		    		->setCarrierShipmentId($shipresult->getShipmentIdentificationNumber())
		    		->setWeightUnits($shipresult->getBillingWeightUnits())
		    		->setWeight($shipresult->getBillingWeight())
		    		->setTrackingNumber($respackage['tracking_number'])
		    		->setCurrencyUnits($shipresult->getCurrencyUnits())
		    		->setTransportationCharge($shipresult->getTransportationShippingCharges())
		    		->setServiceOptionCharge($shipresult->getServiceOptionsShippingCharges())
		    		->setShippingTotal($shipresult->getTotalShippingCharges())
		    		->setNegotiatedTotal($shipresult->getNegotiatedTotalShippingCharges())
		    		->setLabelFormat($respackage['label_image_format'])
		    		->setLabelImage($respackage['label_image'])
		    		->setDateShipped(date('Y-m-d H:i:s'))
		    		->save();
		    	
		    	$retval[] = $pkg;
	    	}
    	}
    	
    	return $retval;
    }
}