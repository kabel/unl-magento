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

    protected $_gatewayUrl = 'https://gateway.fedex.com/web-services';

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
        
        $newline = '';
        
        $wsdlpath = dirname(__FILE__).'/wsdl/RateService_v8.wsdl';
    
        if($this->getConfigData('test_mode'))  {
            $wsdlpath = dirname(__FILE__).'/wsdl/testRateService_v8.wsdl';
        }
        
        ini_set('soap.wsdl_cache_enabled', 1);
        
        $client = new SoapClient($wsdlpath);
        $request = array();
        
        $request['WebAuthenticationDetail'] = array('UserCredential' => array(
            'Key'      => $this->getConfigData('key'),
            'Password' => $this->getConfigData('password')
        ));
        
        $request['ClientDetail'] = array(
            'AccountNumber' => $r->getAccount(),
            'MeterNumber'   => $this->getConfigData('meter')
        );
        
        $request['TransactionDetail'] = array(
            'CustomerTransactionId' => ' *** Rate Request v8 using PHP ***'
        );
        
        $request['Version'] = array(
            'ServiceId' => 'crs', 
            'Major' => '8', 
            'Intermediate' => '0', 
            'Minor' => '0'
        );
        
        $request['RequestedShipment'] = array(
            'DropoffType' => $this->getUnderscoreCodeFromCode($r->getDropoffType()),
            'ShipTimestamp' => date('c'),
            'PackagingType' => $this->getUnderscoreCodeFromCode($r->getPackaging())
        );
        
        if($this->getConfigData('third_party') == 1)  {
            $shipping_charges_payment = array('PaymentType' => 'THIRD_PARTY', 'Payor' => array('AccountNumber' => $this->getConfigData('third_party_fedex_account'), 'CountryCode' => $this->getConfigData('third_party_fedex_account_country')));
        }
        else  {
            $shipping_charges_payment = array('PaymentType' => 'SENDER', 'Payor' => array('AccountNumber' => $this->getConfigData('account'), 'CountryCode' => $this->getConfigData('account_country')));
        }
        
        $shipperStreetLines = array(Mage::getStoreConfig('shipping/origin/address1'));
        if ($temp = Mage::getStoreConfig('shipping/origin/address2')) {
            $shipperStreetLines[] = $temp;
        }
        if ($temp = Mage::getStoreConfig('shipping/origin/address3')) {
            $shipperStreetLines[] = $temp;
        }
        
        $request['RequestedShipment']['Shipper'] = array('Address' => array(
            'StreetLines' => $shipperStreetLines,
            'City' => Mage::getStoreConfig('shipping/origin/city'),
            'StateOrProvinceCode' => Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id'))->getCode(),
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
        $request['RequestedShipment']['PackageCount'] = '1';
        $request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
        $request['RequestedShipment']['RequestedPackageLineItems'] = array('0' => array(
            'InsuredValue' => array(
                'Amount' => $r->getValue(),
                'Currency' => Mage::app()->getBaseCurrencyCode()
            ),
            'Weight' => array(
                'Value' => $r->getWeight(),
                'Units' => 'LB'
            )
        ));
        
        try {
            $response = $client->getRates($request);
            
            if ($response->HighestSeverity == 'FAILURE') {
                $response = null;
            }
        } catch (SoapFault $fault) {
            $response = null;
        }
        
        return $this->_parseXmlResponse($response);
    }

    protected function _parseXmlResponse($response)
    {
        $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
        $allowedUnderscoreMethods = array();
        foreach ($allowedMethods as $method) {
            $allowedUnderscoreMethods[] = $this->getUnderscoreCodeFromCode($method);
        }
        
        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        
        if ($response == null) {
            $msg = $this->getConfigData('specificerrmsg');
            
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($msg);
            $result->append($error);
        } elseif ($response->HighestSeverity == 'ERROR') {
            $msg = '';
            if (is_array($response->Notifications)) {
                foreach ($response->Notifications as $notification) {
                    $msg .= $notification->Severity . ': ' . $notification->Message . "\n";
                }
            } else {
                $msg .= $response->Notifications->Severity . ': ' . $response->Notifications->Message . "\n";
            }
            
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($msg);
            $result->append($error);
        } else {
            foreach ($response->RateReplyDetails as $rateReply) {
                if (in_array($rateReply->ServiceType, $allowedUnderscoreMethods)) {
                    $_serviceType = str_replace('_', '', $rateReply->ServiceType);
                    
                    $rate = Mage::getModel('shipping/rate_result_method');
                    $rate->setCarrier('fedex');
                    $rate->setCarrierTitle($this->getConfigData('title'));
                    $rate->setMethod($_serviceType);
                    $rate->setMethodTitle($this->getCode('method', $rateReply->ServiceType, true));
                    $rate->setCost($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount);
                    $rate->setPrice($this->getMethodPrice(floatval($rateReply->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount), $_serviceType));
                    $result->append($rate);
                }
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
                'FEDEX_2_DAY'                        => Mage::helper('usa')->__('2Day'),
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
        $r->setMeter($this->getConfigData('meter'));

        $this->_rawTrackingRequest = $r;

    }
    protected function _getXMLTracking($tracking)
    {
        $r = $this->_rawTrackingRequest;
        
        ini_set('soap.wsdl_cache_enabled', 1);
        
        //get the path to the WSDL
        $wsdlpath = dirname(__FILE__).'/wsdl/TrackService_v4.wsdl';
    
        if($this->getConfigData('test_mode'))  {
            $wsdlpath = dirname(__FILE__).'/wsdl/testTrackService_v4.wsdl';
        }
        
        $client = new SoapClient($wsdlpath);
        $request = array();
        
        $request['WebAuthenticationDetail'] = array('UserCredential' => array(
            'Key' => $this->getConfigData('key'),
            'Password' => $this->getConfigData('password')
        ));
        
        $request['ClientDetail'] = array(
            'AccountNumber' => $r->getAccount(),
            'MeterNumber' => $r->getMeter()
        );
        
        $request['TransactionDetail'] = array('CustomerTransactionId' => '*** Track Request v4 using PHP ***');
        
        $request['Version'] = array('ServiceId' => 'trck', 'Major' => '4', 'Intermediate' => '0', 'Minor' => '0');
        
        $request['PackageIdentifier'] = array(
            'Value' => $tracking,
            'Type' => 'TRACKING_NUMBER_OR_DOORTAG'
        );
        
        $request['IncludeDetailedScans'] = true;
        
        try {
            $response = $client->track($request);
        } catch (SoapFault $fault) {
            $response = null;
        }
        
        $this->_parseXmlTrackingResponse($tracking, $response);
    }

    protected function _parseXmlTrackingResponse($trackingvalue,$response)
    {
        if(!$this->_result){
             $this->_result = Mage::getModel('shipping/tracking_result');
         }
         $defaults = $this->getDefaults();
        
        if (null == $response) {
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage(Mage::helper('usa')->__('Unable to retrieve tracking'));
            $this->_result->append($error);
        } elseif ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
            $msg = '';
            if(is_array($response->Notifications))  {
                foreach ($response->Notifications as $notification)  {
                    $msg .= $notification->Severity.': '.$notification->Message."\n";
                }
            } else {
                $msg .= $response->Notifications->Severity.': '.$response->Notifications->Message."\n";
            }
            
            $error = Mage::getModel('shipping/tracking_result_error');
            $error->setCarrier('fedex');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setTracking($trackingvalue);
            $error->setErrorMessage($msg);
            $this->_result->append($error);
        } else {
            $resultArr = array(
                'status' => $response->TrackDetails->StatusDescription,
                'service' => $response->TrackDetails->ServiceInfo,
                'deliverydate' => $response->TrackDetails->EstimatedDeliveryTimestamp,
                'deliverytime' => $response->TrackDetails->EstimatedDeliveryTimestamp,
                'deliverylocation' => $response->TrackDetails->DeliveryLocationDescription,
                'signedby' => $response->TrackDetails->DeliverySignatureName,
                'shippeddate' => $response->TrackDetails->ShipTimestamp,
                'weight' => $response->TrackDetails->ShipmentWeight->Value . ' ' . $response->TrackDetails->ShipmentWeight->Units,
                'progressdetail' => array()
            );
            foreach ($response->TrackDetails->Events as $evt) {
                $resultArr['progressdetail'][] = array(
                    'activity' => $evt->EventDescription,
                    'deliverydate' => $evt->Timestamp,
                    'deliverytime' => $evt->Timestamp,
                    'deliverylocation' => $evt->Address->City . ', ' . $evt->Address->StateOrProvinceCode . ', ' . $evt->Address->CountryCode
                );
            }
            
            $tracking = Mage::getModel('shipping/tracking_result_status');
            $tracking->setCarrier('fedex');
            $tracking->setCarrierTitle($this->getConfigData('title'));
            $tracking->setTracking($trackingvalue);
            $tracking->addData($resultArr);
            $this->_result->append($tracking);
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
    	$wsdlpath = dirname(__FILE__).'/wsdl/ShipService_v8.wsdl';
  	
    	if($store->getConfig('carriers/fedex/test_mode'))  {
    		$wsdlpath = dirname(__FILE__).'/wsdl/testShipService_v8.wsdl';
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
		$request['Version'] = array('ServiceId' => 'ship', 'Major' => 8, 'Intermediate' => 0, 'Minor' => 0);
		$request['RequestedShipment'] = array('ShipTimestamp' => date('c'),
			'DropoffType' => $this->getUnderscoreCodeFromCode($store->getConfig('carriers/fedex/dropoff')), // valid values REGULAR_PICKUP, REQUEST_COURIER, DROP_BOX, BUSINESS_SERVICE_CENTER and STATION
			'ServiceType' => $servicetype,
			'PackagingType' => $package->getContainerCode(), // valid values FEDEX_BOK, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
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
					'CountryCode' => $shipaddress->getCountryId(),
		            'Residential' => $store->getConfig('carriers/fedex/residence_delivery'))),
			'ShippingChargesPayment' => $shipping_charges_payment,
			'LabelSpecification' => array('LabelFormatType' => 'COMMON2D', // valid values COMMON2D, LABEL_DATA_ONLY
				'ImageType' => 'PNG', // valid values DPL, EPL2, PDF, ZPLII and PNG
				'LabelStockType' => 'PAPER_4X6'
		
				//TODO: Add return address functionality using PrintedLabelOrigin
				
			),
			'RateRequestTypes' => array('ACCOUNT'), // valid values ACCOUNT and LIST
			'PackageCount' => 1,
			'PackageDetail' => 'INDIVIDUAL_PACKAGES',
			'RequestedPackageLineItems' => array('0' => array(
			    'Weight' => array('Value' => sprintf("%01.1f", round($package->getWeight(), 1)), 'Units' => substr($package->getWeightUnitCode(), 0, 2)), // valid values LB or KG
				'CustomerReferences' => array('0' => array('CustomerReferenceType' => 'CUSTOMER_REFERENCE', 'Value' => $orderid)), // valid values CUSTOMER_REFERENCE, INVOICE_NUMBER, P_O_NUMBER and SHIPMENT_INTEGRITY
				'ContentRecords' => $contents
			))
		);
		
    	//dimensions
    	if($package->getDimensionUnitCode())  {
    		$request['RequestedShipment']['RequestedPackageLineItems'][0]['Dimensions'] = array('Length' => $package->getLength(), 'Width' => $package->getWidth(), 'Height' => $package->getHeight(),
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
			$shipmentid = $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds->TrackingNumber;
			$result->setShipmentIdentificationNumber($shipmentid);
			
			//get package data
			$packages = array();
			$packages[] = array(
                	'package_number' => 1,
                	'tracking_number' => $shipmentid,
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
					->setTitle($this->getConfigData('title'))
					->setNumber($respackage['tracking_number'])
					->setShipment($shipment);
				$shipment->addTrack($track);
				
				//save the shipment
				$shipment->register();
				$shipment->setEmailSent(true);
                $shipment->getOrder()->setCustomerNoteNotify(true);
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
