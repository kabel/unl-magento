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

//international form types
define('INTERNATIONAL_FORM_TYPE_INVOICE', 1);
define('INTERNATIONAL_FORM_TYPE_SHIPPERS_EXPORT_DECLARATION', 2);
define('INTERNATIONAL_FORM_TYPE_CERTIFICATE_OF_ORIGIN', 3);
define('INTERNATIONAL_FORM_TYPE_NAFTA_CERTIFICATE_OF_ORIGIN', 4);


/**
 * UPS shipping rates estimation
 *
 * @category   Mage
 * @package    Mage_Usa
 */
class Zenprint_Ordership_Model_Shipping_Carrier_Ups
    extends Mage_Usa_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface, Zenprint_Ordership_Model_Shipping_Carrier_Xmlship_Interface
{

    protected $_code = 'ups';

    protected $_request = null;

    protected $_shiprequest = null;

    protected $_result = null;

    protected $_xmlAccessRequest = null;

    protected $_defaultCgiGatewayUrl = 'http://www.ups.com:80/using/services/rave/qcostcgi.cgi';

    // see UPS Developer Guide for infomartion about error codes
    protected $_retryErrors = array(
        120203,
        120213,
        120217
    );

    protected $_errorCodes = array();

    /**
     * Retrieves the dimension units for this carrier and store
     *
     * @return string IN or CM
     */
    public function getDimensionUnits()  {
    	return Mage::getStoreConfig('carriers/ups/dimension_units', $this->getStore());
    }

	/**
     * Retrieves the weight units for this carrier and store
     *
     * @return string LBS or KGS
     */
    public function getWeightUnits()  {
    	return Mage::getStoreConfig('carriers/ups/unit_of_measure', $this->getStore());
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

    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        $this->_request = $request;

        $r = new Varien_Object();

        if ($request->getLimitMethod()) {
            $r->setAction($this->getCode('action', 'single'));
            $r->setProduct($request->getLimitMethod());
        } else {
            $r->setAction($this->getCode('action', 'all'));
            $r->setProduct('GND'.$this->getConfigData('dest_type'));
        }

        if ($request->getUpsPickup()) {
            $pickup = $request->getUpsPickup();
        } else {
            $pickup = $this->getConfigData('pickup');
        }
        $r->setPickup($this->getCode('pickup', $pickup));

        if ($request->getUpsContainer()) {
            $container = $request->getUpsContainer();
        } else {
            $container = $this->getConfigData('container');
        }
        $r->setContainer($this->getCode('container', $container));

        if ($request->getUpsDestType()) {
            $destType = $request->getUpsDestType();
        } else {
            $destType = $this->getConfigData('dest_type');
        }
        $r->setDestType($this->getCode('dest_type', $destType));

        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = Mage::getStoreConfig('shipping/origin/country_id', $this->getStore());
        }

        $r->setOrigCountry(Mage::getModel('directory/country')->load($origCountry)->getIso2Code());

        if ($request->getOrigRegionCode()) {
            $origRegionCode = $request->getOrigRegionCode();
        } else {
            $origRegionCode = Mage::getStoreConfig('shipping/origin/region_id', $this->getStore());
            if (is_numeric($origRegionCode)) {
                $origRegionCode = Mage::getModel('directory/region')->load($origRegionCode)->getCode();
            }
        }
        $r->setOrigRegionCode($origRegionCode);

        if ($request->getOrigPostcode()) {
            $r->setOrigPostal($request->getOrigPostcode());
        } else {
            $r->setOrigPostal(Mage::getStoreConfig('shipping/origin/postcode', $this->getStore()));
        }

        if ($request->getOrigCity()) {
            $r->setOrigCity($request->getOrigCity());
        } else {
            $r->setOrigCity(Mage::getStoreConfig('shipping/origin/city', $this->getStore()));
        }


        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }

        //for UPS, puero rico state for US will assume as puerto rico country
        if ($destCountry==self::USA_COUNTRY_ID && ($request->getDestPostcode()=='00912' || $request->getDestRegionCode()==self::PUERTORICO_COUNTRY_ID)) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }

        $r->setDestCountry(Mage::getModel('directory/country')->load($destCountry)->getIso2Code());

        $r->setDestRegionCode($request->getDestRegionCode());

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        } else {

        }

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight());
        $r->setWeight($weight);
        if ($request->getFreeMethodWeight()!=$request->getPackageWeight()) {
            $r->setFreeMethodWeight($request->getFreeMethodWeight());
        }

        $r->setValue($request->getPackageValue());
        $r->setValueWithDiscount($request->getPackageValueWithDiscount());

        if ($request->getUpsUnitMeasure()) {
            $unit = $request->getUpsUnitMeasure();
        } else {
            $unit = $this->getConfigData('unit_of_measure');
        }
        $r->setUnitMeasure($unit);

        $this->_rawRequest = $r;

        return $this;
    }

    public function getResult()
    {
       return $this->_result;
    }

    protected function _getQuotes()
    {
        switch ($this->getConfigData('type')) {
            case 'UPS':
               return $this->_getCgiQuotes();

            case 'UPS_XML':
               return $this->_getXmlQuotes();
        }
        return null;
    }

    protected function _setFreeMethodRequest($freeMethod)
    {
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($r->getFreeMethodWeight());
        $r->setWeight($weight);
        $r->setAction($this->getCode('action', 'single'));
        $r->setProduct($freeMethod);
    }

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
            '19_destPostal'  => $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
            'weight_std'     => strtolower($r->getUnitMeasure()),
        );
        $params['47_rate_chart'] = $params['47_rate_chart']['label'];

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
        } catch (Exception $e) {
            $responseBody = '';
        }

        return $this->_parseCgiResponse($responseBody);
    }

    public function getShipmentByCode($code,$origin = null){
        if($origin===null){
            $origin = $this->getConfigData('origin_shipment');
        }
        $arr = $this->getCode('originShipment',$origin);
        if(isset($arr[$code]))
            return $arr[$code];
        else
            return false;
    }


    protected function _parseCgiResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        $errorTitle = Mage::helper('usa')->__('Unknown error');
        if (strlen(trim($response))>0) {
            $rRows = explode("\n", $response);
            $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));
            foreach ($rRows as $rRow) {
                $r = explode('%', $rRow);
                switch (substr($r[0],-1)) {
                    case 3: case 4:
                        if (in_array($r[1], $allowedMethods)) {
                            $costArr[$r[1]] = $r[8];
                            $priceArr[$r[1]] = $this->getMethodPrice($r[8], $r[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $r[1];
                        break;
                    case 6:
                        if (in_array($r[3], $allowedMethods)) {
                            $costArr[$r[3]] = $r[10];
                            $priceArr[$r[3]] = $this->getMethodPrice($r[10], $r[3]);
                        }
                        break;
                }
            }
            asort($priceArr);
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            //$error->setErrorMessage($errorTitle);
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method=>$price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $method_arr = $this->getCode('method', $method);
                $rate->setMethodTitle(Mage::helper('usa')->__($method_arr));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }
#echo "<pre>".print_r($result,1)."</pre>";
        return $result;
    }

/*
    public function isEligibleForFree($method)
    {
        return $method=='GND' || $method=='GNDCOM' || $method=='GNDRES';
    }
*/

    public function getCode($type, $code='')
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

        $codes = array(
            'action'=>array(
                'single'=>'3',
                'all'=>'4',
            ),

            'originShipment'=>array(
                // United States Domestic Shipments
                'United States Domestic Shipments' => array(
                    '01' => Mage::helper('usa')->__('UPS Next Day Air'),
                    '02' => Mage::helper('usa')->__('UPS Second Day Air'),
                    '03' => Mage::helper('usa')->__('UPS Ground'),
                    '07' => Mage::helper('usa')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('usa')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '12' => Mage::helper('usa')->__('UPS Three-Day Select'),
                    '13' => Mage::helper('usa')->__('UPS Next Day Air Saver'),
                    '14' => Mage::helper('usa')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('usa')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Shipments Originating in United States
                'Shipments Originating in United States' => array(
                    '01' => Mage::helper('usa')->__('UPS Next Day Air'),
                    '02' => Mage::helper('usa')->__('UPS Second Day Air'),
                    '03' => Mage::helper('usa')->__('UPS Ground'),
                    '07' => Mage::helper('usa')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('usa')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '12' => Mage::helper('usa')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('usa')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express Plus'),
                    '59' => Mage::helper('usa')->__('UPS Second Day Air A.M.'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Shipments Originating in Canada
                'Shipments Originating in Canada' => array(
                    '01' => Mage::helper('usa')->__('UPS Express'),
                    '02' => Mage::helper('usa')->__('UPS Expedited'),
                    '07' => Mage::helper('usa')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('usa')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '12' => Mage::helper('usa')->__('UPS Three-Day Select'),
                    '14' => Mage::helper('usa')->__('UPS Express Early A.M.'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Shipments Originating in the European Union
                'Shipments Originating in the European Union' => array(
                    '07' => Mage::helper('usa')->__('UPS Express'),
                    '08' => Mage::helper('usa')->__('UPS Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express PlusSM'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Polish Domestic Shipments
                'Polish Domestic Shipments' => array(
                    '07' => Mage::helper('usa')->__('UPS Express'),
                    '08' => Mage::helper('usa')->__('UPS Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                    '82' => Mage::helper('usa')->__('UPS Today Standard'),
                    '83' => Mage::helper('usa')->__('UPS Today Dedicated Courrier'),
                    '84' => Mage::helper('usa')->__('UPS Today Intercity'),
                    '85' => Mage::helper('usa')->__('UPS Today Express'),
                    '86' => Mage::helper('usa')->__('UPS Today Express Saver'),
                ),
                // Puerto Rico Origin
                'Puerto Rico Origin' => array(
                    '01' => Mage::helper('usa')->__('UPS Next Day Air'),
                    '02' => Mage::helper('usa')->__('UPS Second Day Air'),
                    '03' => Mage::helper('usa')->__('UPS Ground'),
                    '07' => Mage::helper('usa')->__('UPS Worldwide Express'),
                    '08' => Mage::helper('usa')->__('UPS Worldwide Expedited'),
                    '14' => Mage::helper('usa')->__('UPS Next Day Air Early A.M.'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Shipments Originating in Mexico
                'Shipments Originating in Mexico' => array(
                    '07' => Mage::helper('usa')->__('UPS Express'),
                    '08' => Mage::helper('usa')->__('UPS Expedited'),
                    '54' => Mage::helper('usa')->__('UPS Express Plus'),
                    '65' => Mage::helper('usa')->__('UPS Saver'),
                ),
                // Shipments Originating in Other Countries
                'Shipments Originating in Other Countries' => array(
                    '07' => Mage::helper('usa')->__('UPS Express'),
                    '08' => Mage::helper('usa')->__('UPS Worldwide Expedited'),
                    '11' => Mage::helper('usa')->__('UPS Standard'),
                    '54' => Mage::helper('usa')->__('UPS Worldwide Express Plus'),
                    '65' => Mage::helper('usa')->__('UPS Saver')
                )
            ),

            'method'=>array(
                '1DM'    => Mage::helper('usa')->__('Next Day Air Early AM'),
                '1DML'   => Mage::helper('usa')->__('Next Day Air Early AM Letter'),
                '1DA'    => Mage::helper('usa')->__('Next Day Air'),
                '1DAL'   => Mage::helper('usa')->__('Next Day Air Letter'),
                '1DAPI'  => Mage::helper('usa')->__('Next Day Air Intra (Puerto Rico)'),
                '1DP'    => Mage::helper('usa')->__('Next Day Air Saver'),
                '1DPL'   => Mage::helper('usa')->__('Next Day Air Saver Letter'),
                '2DM'    => Mage::helper('usa')->__('2nd Day Air AM'),
                '2DML'   => Mage::helper('usa')->__('2nd Day Air AM Letter'),
                '2DA'    => Mage::helper('usa')->__('2nd Day Air'),
                '2DAL'   => Mage::helper('usa')->__('2nd Day Air Letter'),
                '3DS'    => Mage::helper('usa')->__('3 Day Select'),
                'GND'    => Mage::helper('usa')->__('Ground'),
                'GNDCOM' => Mage::helper('usa')->__('Ground Commercial'),
                'GNDRES' => Mage::helper('usa')->__('Ground Residential'),
                'STD'    => Mage::helper('usa')->__('Canada Standard'),
                'XPR'    => Mage::helper('usa')->__('Worldwide Express'),
                'WXS'    => Mage::helper('usa')->__('Worldwide Express Saver'),
                'XPRL'   => Mage::helper('usa')->__('Worldwide Express Letter'),
                'XDM'    => Mage::helper('usa')->__('Worldwide Express Plus'),
                'XDML'   => Mage::helper('usa')->__('Worldwide Express Plus Letter'),
                'XPD'    => Mage::helper('usa')->__('Worldwide Expedited'),
            ),

            //reverse of above
            //TODO: This is temporary to show in the ordership details...needs to read from methods above.
            'code_method'=>array(
				'14' 	=> 	'1DM',
                '14'	=>	'1DML',
                '01'	=>	'1DA',
                '01'	=>	'1DAL',
                '01'	=>	'1DAPI',
                '13'	=>	'1DP',
                '13'	=>	'1DPL',
                '59'	=>	'2DM',
                '59'	=>	'2DML',
                '02'	=>	'2DA',
                '02'	=>	'2DAL',
                '12'	=>	'3DS',
                '03'	=>	'GND',
                '03'	=>	'GNDCOM',
                '03'	=>	'GNDRES',
                '11'	=>	'STD',
                '07'	=>	'XPR',
                '07'	=>	'WXS',
                '07'	=>	'XPRL',
                '54'	=>	'XDM',
                '54'	=>	'XDML',
                '08'	=>	'XPD',
            ),

            'method' => array(
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

            'pickup'=>array(
                'RDP'    => array("label"=>'Regular Daily Pickup',"code"=>"01"),
                'OCA'    => array("label"=>'On Call Air',"code"=>"07"),
                'OTP'    => array("label"=>'One Time Pickup',"code"=>"06"),
                'LC'     => array("label"=>'Letter Center',"code"=>"19"),
                'CC'     => array("label"=>'Customer Counter',"code"=>"03"),
            ),

            'container'=>array(
                'CP'     => '00', // Customer Packaging
                'ULE'    => '01', // UPS Letter Envelope
                'UT'     => '03', // UPS Tube
                'UEB'    => '21', // UPS Express Box
                'UW25'   => '24', // UPS Worldwide 25 kilo
                'UW10'   => '25', // UPS Worldwide 10 kilo
            ),

            'container_description'=>array(
                'CP'     => Mage::helper('usa')->__('Customer Packaging'),
                'ULE'    => Mage::helper('usa')->__('UPS Letter Envelope'),
                'UT'     => Mage::helper('usa')->__('UPS Tube'),
                'UEB'    => Mage::helper('usa')->__('UPS Express Box'),
                'UW25'   => Mage::helper('usa')->__('UPS Worldwide 25 kilo'),
                'UW10'   => Mage::helper('usa')->__('UPS Worldwide 10 kilo'),
            ),

            //These are used for the ShipXML to determine what kind of package is sent....not sure why diff from 'container' above
            'package_type'=>array(
            	'02' => 'Customer Supplied Package',
            	'01' => 'UPS Letter',
            	'03' => 'Tube',
            	'04' => 'PAK',
            	'21' => 'UPS Express Box',
            	'2a' => 'UPS Small Express Box',
            	'2b' => 'UPS Medium Express Box',
            	'2c' => 'UPS Large Express Box',
            	'24' => 'UPS 25KG Box',
            	'25' => 'UPS 10KG Box',
            	'30' => 'Pallet',
            ),

            //these are dimensions in centimeters for each package type
            'package_dimensions_cm' => array(
            	//Customer Supplied Package
            	'02' => array(
            		'height' => $cdef_height_cm,
            		'width' => $cdef_width_cm,
            		'length' => $cdef_length_cm,
            	),
            	//UPS Letter
            	'01' => array(
            		'height' => 0,
            		'width' => 9.5 * 2.54,
            		'length' => 12.5 * 2.54,
            	),
            	//Tube
            	'03' => array(
            		'height' => 38 * 2.54,
            		'width' => 6 * 2.54,
            		'length' => 6 * 2.54,
            	),
            	//PAK
            	'04' => array(
            		'height' => 0,
            		'width' => 12.75 * 2.54,
            		'length' => 16 * 2.54,
            	),
            	//UPS Express Box (defining same as small)
            	'21' => array(
            		'height' => 2 * 2.54,
            		'width' => 11 * 2.54,
            		'length' => 13 * 2.54,
            	),
            	//UPS Small Express Box
            	'2a' => array(
            		'height' => 2 * 2.54,
            		'width' => 11 * 2.54,
            		'length' => 13 * 2.54,
            	),
            	//UPS Medium Express Box
            	'2b' => array(
            		'height' => 3 * 2.54,
            		'width' => 11 * 2.54,
            		'length' => 15 * 2.54,
            	),
            	//UPS Large Express Box
            	'2c' => array(
            		'height' => 3 * 2.54,
            		'width' => 13 * 2.54,
            		'length' => 18 * 2.54,
            	),
            	//UPS 25KG Box
            	'24' => array(
            		'height' => 14 * 2.54,
            		'width' => 17.38 * 2.54,
            		'length' => 19.38 * 2.54,
            	),
            	//UPS 10KG Box
            	'25' => array(
            		'height' => 10.75 * 2.54,
            		'width' => 13.25 * 2.54,
            		'length' => 16.5 * 2.54,
            	),
            	//Pallet
            	'30' => array(
            		'height' => 120,
            		'width' => 160,
            		'length' => 200,
            	),
            ),

            //these are dimensions in inches for each package type
            'package_dimensions_in' => array(
            	//Customer Supplied Package
            	'02' => array(
            		'height' => $cdef_height_in,
            		'width' => $cdef_width_in,
            		'length' => $cdef_length_in,
            	),
            	//UPS Letter
            	'01' => array(
            		'height' => 0,
            		'width' => 9.5,
            		'length' => 12.5,
            	),
            	//Tube
            	'03' => array(
            		'height' => 6,
            		'width' => 6,
            		'length' => 38,
            	),
            	//PAK
            	'04' => array(
            		'height' => 0,
            		'width' => 12.75,
            		'length' => 16,
            	),
            	//UPS Express Box (defining same as small)
            	'21' => array(
            		'height' => 2,
            		'width' => 11,
            		'length' => 13,
            	),
            	//UPS Small Express Box
            	'2a' => array(
            		'height' => 2,
            		'width' => 11,
            		'length' => 13,
            	),
            	//UPS Medium Express Box
            	'2b' => array(
            		'height' => 3,
            		'width' => 11,
            		'length' => 15,
            	),
            	//UPS Large Express Box
            	'2c' => array(
            		'height' => 3,
            		'width' => 13,
            		'length' => 18,
            	),
            	//UPS 25KG Box
            	'24' => array(
            		'height' => 14,
            		'width' => 17.38,
            		'length' => 19.38,
            	),
            	//UPS 10KG Box
            	'25' => array(
            		'height' => 10.75,
            		'width' => 13.25,
            		'length' => 16.5,
            	),
            	//Pallet
            	'30' => array(
            		'height' => 47.24,
            		'width' => 62.99,
            		'length' => 78.74,
            	),
            ),

            'dest_type'=>array(
                'RES'    => '01', // Residential
                'COM'    => '02', // Commercial
            ),

            'dest_type_description'=>array(
                'RES'    => Mage::helper('usa')->__('Residential'),
                'COM'    => Mage::helper('usa')->__('Commercial'),
            ),

            'unit_of_measure'=>array(
                'LBS'   =>  Mage::helper('usa')->__('Pounds'),
                'KGS'   =>  Mage::helper('usa')->__('Kilograms'),
            ),

            'unit_of_dimension'=>array(
                'IN'   =>  Mage::helper('usa')->__('Inches'),
                'CM'   =>  Mage::helper('usa')->__('Centimeters'),
            ),

        );

        if (!isset($codes[$type])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code type: %s', $type));
            return false;
        } elseif (''===$code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
//            throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid UPS CGI code for type %s: %s', $type, $code));
            return false;
        } else {
            return $codes[$type][$code];
        }
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
            '19_destPostal'  => 'US' == $r->getDestCountry() ? substr($r->getDestPostal(), 0, 5) : $r->getDestPostal(),
            '22_destCountry' => $r->getDestCountry(),
            'destRegionCode' => $r->getDestRegionCode(),
            '23_weight'      => $r->getWeight(),
            '47_rate_chart'  => $r->getPickup(),
            '48_container'   => $r->getContainer(),
            '49_residential' => $r->getDestType(),
        );
        $params['10_action'] = $params['10_action']=='4'? 'Shop' : 'Rate';
        $serviceCode = $r->getProduct() ? $r->getProduct() : '';
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
    <Service>
        <Code>{$serviceCode}</Code>
        <Description>{$serviceDescription}</Description>
    </Service>
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
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

          $xmlRequest .= ($params['49_residential']==='01' ? '<ResidentialAddressIndicator/>' : '');

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
    </Package>
XMLRequest;
        if ($this->getConfigFlag('negotiated_active')) {
            $xmlRequest .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
        }

$xmlRequest .= <<< XMLRequest
  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;

//        $debugData = array('request' => $xmlRequest);
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
//            $debugData['result'] = $xmlResponse;
            curl_close($ch);
        } catch (Exception $e) {
//            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

//        $this->_debug($debugData);
        return $this->_parseXmlResponse($xmlResponse);
    }

    protected function _parseXmlResponse($xmlResponse)
    {
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($xmlResponse))>0) {
            $xml = new Varien_Simplexml_Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0][0];
            if($success===1){
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");
                $allowedMethods = explode(",", $this->getConfigData('allowed_methods'));

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $this->getConfigFlag('negotiated_active')
                    && $this->getConfigData('shipper_number')
                    && !empty($negotiatedArr);

                foreach ($arr as $shipElement){
                    $code = (string)$shipElement->Service->Code;
                    #$shipment = $this->getShipmentByCode($code);
                    if (in_array($code, $allowedMethods)) {

                        if ($negotiatedActive) {
                            $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        } else {
                            $cost = $shipElement->TotalCharges->MonetaryValue;
                        }

                        $costArr[$code] = $cost;
                        $priceArr[$code] = $this->getMethodPrice(floatval($cost),$code);
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier('ups');
                $error->setCarrierTitle($this->getConfigData('title'));
                //$error->setErrorMessage($errorTitle);
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        $defaults = $this->getDefaults();
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            if(!isset($errorTitle)){
                $errorTitle = Mage::helper('usa')->__('Cannot retrieve shipping rates');
            }
            //$error->setErrorMessage($errorTitle);
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

    public function getTracking($trackings)
    {
        $return = array();

        if (!is_array($trackings)) {
            $trackings = array($trackings);
        }

        if ($this->getConfigData('type')=='UPS') {
            $this->_getCgiTracking($trackings);
        } elseif ($this->getConfigData('type')=='UPS_XML'){
            $this->setXMLAccessRequest();
            $this->_getXmlTracking($trackings);
        }

        return $this->_result;
    }

    protected function setXMLAccessRequest()
    {
        $userid = $this->getConfigData('username');
        $userid_pass = $this->getConfigData('password');
        $access_key = $this->getConfigData('access_license_number');

        $this->_xmlAccessRequest =  <<<XMLAuth
<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
  <AccessLicenseNumber>$access_key</AccessLicenseNumber>
  <UserId>$userid</UserId>
  <Password>$userid_pass</Password>
</AccessRequest>
XMLAuth;
    }

    protected function _getCgiTracking($trackings)
    {
        //ups no longer support tracking for data streaming version
        //so we can only reply the popup window to ups.
        $result = Mage::getModel('shipping/tracking_result');
        $defaults = $this->getDefaults();
        foreach($trackings as $tracking){
            $status = Mage::getModel('shipping/tracking_result_status');
            $status->setCarrier('ups');
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl("http://wwwapps.ups.com/WebTracking/processInputRequest?HTMLVersion=5.0&error_carried=true&tracknums_displayed=5&TypeOfInquiryNumber=T&loc=en_US&InquiryNumber1=$tracking&AgreeToTermsAndConditions=yes");
            $result->append($status);
        }

        $this->_result = $result;
        return $result;
    }

    protected function _getXmlTracking($trackings)
    {
        $url = $this->getConfigData('tracking_xml_url');

        foreach($trackings as $tracking){
            $xmlRequest=$this->_xmlAccessRequest;

/*
* RequestOption==>'activity' or '1' to request all activities
*/
$xmlRequest .=  <<<XMLAuth
<?xml version="1.0" ?>
<TrackRequest xml:lang="en-US">
    <Request>
        <RequestAction>Track</RequestAction>
        <RequestOption>activity</RequestOption>
    </Request>
    <TrackingNumber><![CDATA[$tracking]]></TrackingNumber>
    <IncludeFreight>01</IncludeFreight>
</TrackRequest>
XMLAuth;
//            try {
                $ch = curl_init();
               	curl_setopt($ch, CURLOPT_URL, $url);
            	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            	curl_setopt($ch, CURLOPT_HEADER, 0);
            	curl_setopt($ch, CURLOPT_POST, 1);
            	curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
            	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            	$xmlResponse = curl_exec ($ch);
//            	curl_close ($ch);
//            }catch (Exception $e) {
//                $xmlResponse = '';
//                curl_close ($ch);
//            }
			//check for error
			if(curl_error($ch))  {
				throw new Exception(Mage::helper('ordership')->__('Error connecting to API:  ').curl_error($ch));
			}
			curl_close($ch);

            $this->_parseXmlTrackingResponse($tracking, $xmlResponse);
        }

        return $this->_result;
    }

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
                $resultArr['shippeddate'] = (string)$arr[0];

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

                        if($i==1){
                           $resultArr['status'] = (string)$activityTag->Status->StatusType->Description;
                           $resultArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $resultArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           $resultArr['deliverylocation'] = (string)$activityTag->ActivityLocation->Description;
                           $resultArr['signedby'] = (string)$activityTag->ActivityLocation->SignedForByName;
                           if ($addArr) {
                            $resultArr['deliveryto']=implode(', ',$addArr);
                           }
                        }else{
                           $tempArr=array();
                           $tempArr['activity'] = (string)$activityTag->Status->StatusType->Description;
                           $tempArr['deliverydate'] = implode('-',$dateArr);//YYYY-MM-DD
                           $tempArr['deliverytime'] = implode(':',$timeArr);//HH:MM:SS
                           if ($addArr) {
                            $tempArr['deliverylocation']=implode(', ',$addArr);
                           }
                           $packageProgress[] = $tempArr;
                        }
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

    public function getResponse()
    {
        $statuses = '';
        if ($this->_result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($trackings = $this->_result->getAllTrackings()) {
                foreach ($trackings as $tracking){
                    if($data = $tracking->getAllData()){
                        if (isset($data['status'])) {
                            $statuses .= Mage::helper('usa')->__($data['status']);
                        } else {
                            $statuses .= Mage::helper('usa')->__($data['error_message']);
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

    /****************************** New Methods for Shipping *************************/

	public function setShipRequest(Zenprint_Ordership_Model_Shipment_Request $request)
    {
        $this->_shiprequest = $request;

        return $this;
    }

    /**
     * Creates a shipment to be sent. Will initialize the shipment, retrieve tracking number, and get shipping label
     *
     */
    public function createShipment(Zenprint_Ordership_Model_Shipment_Request $request)  {
    	$order = Mage::getModel('sales/order')->loadByIncrementId($request->getOrderId());

    	if (!$order->getStore()->getConfig('carriers/ups/active'))  {
    		throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('UPS shipping is currently disabled for this order\'s store.'));
    		return false;
        }

        $this->setShipRequest($request);

        $this->_result = $this->_createShipment();

        return $this->_result;

    }

	protected function _createShipment()  {
		$order = Mage::getModel('sales/order')->loadByIncrementId($this->_shiprequest->getOrderId());

		switch ($order->getStore()->getConfig('carriers/ups/type')) {
            case 'UPS':
            	throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('UPS shipments can only be automatically created when \'United Parcel Service XML\' is configured as the \'UPS type\'.'));
				return false;
			//currently only available through XML
            case 'UPS_XML':
				return $this->_createXmlShipment();
        }
        return null;
    }

    /**
     * Sends the ShipmentConfirmRequest message.
     *
     * @param string $url URL to submit request to.
     * @param int $orderid The order_id that the items being shipped belong to.
     * @param array $packages An array of Zenprint_Shipping_Model_Shipment_Package objects.
     * @return Mage_Shipping_Model_Shipment_Confirmation The ShipmentConfirmationResponse object
     */
    protected function _sendXmlShipmentConfirmRequest($orderid, $packages, $url=null)  {

    	//get the order information
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
        if(empty($order) || $order->getEntityId() == null)  {
        	throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid order id.'));
        	return false;
        }
        $store = $order->getStore();
        $storeid = $store->getId();

    	if(empty($url))  {
    		$url = rtrim($store->getConfig('carriers/ups/shipping_xml_url'), '/').'/ShipConfirm';
    	}

        $shipaddress = $order->getShippingAddress();  //Mage_Sales_Model_Order_Address
    	if(empty($shipaddress))  {
        	throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('No shipping address for order.'));
        	return false;
        }

        //get the package data
        $pkgs = array();
        $invoicetotal = 0;
        $invoicetotalpkgtype = true;
        foreach ($packages as $pkg)  {
        	$p = array(
        		'description' => $pkg->getDescription(),  //optional description of the package
				'package_code' => $pkg->getContainerCode(),  //2 char, code for packaging type, default is '02' - customer supplied package
				'package_description' => $this->getCode('package_type', $pkg->getContainerCode()),  //description of packaging, default 'customer supplied package
				'weight_unitcode' => $pkg->getWeightUnitCode(),  //units for weight, can be either 'LBS' or 'KGS'
				'weight' => sprintf("%01.1f", round($pkg->getWeight(), 1)),  //5 chars, up to 1 digit after decimal
        		'dimension_unitcode' => $pkg->getDimensionUnitCode(),  //units for dimension, can be either 'IN' or 'CM'
        		'height' => $pkg->getHeight(),
        		'width' => $pkg->getWidth(),
        		'length' => $pkg->getLength(),
				'reference_code' => 'TN',  //2 chars, optional - default 'TN' (Transaction Reference Number) - Order ID
				'reference_value' => substr($orderid, 0, 35), //35 chars, optional - order_id
        	);
        	//reference codes can only be used in US domestic shipments
        	if($shipaddress->getCountryId() != 'US' || $store->getConfig('shipping/origin/country_id') != 'US')  {
        		unset($p['reference_code']);
        		unset($p['reference_value']);
        	}

        	//if package has confirmation
        	if($pkg->getConfirmationCode != null)  {
        		$p['confirmation_type'] = $pkg->getConfirmationCode();  //'1' (delivery confirmation), '2' (signature required), or '3' (adult signature required)
        		$p['confirmation_number'] = $pkg->getConfirmationNumber();  //delivery confirmation control number
        	}
        	//if package insured
        	if($pkg->getInsuranceCode() != null)  {
        		$p['insurance_type'] = $pkg->getInsuranceCode();  //2 char, '01' (EVS Declared Value), or '02' (DVS Shipper Declared Value), UPS default is '01'
        		$p['insurance_currencycode'] = $pkg->getInsuranceCurrencyCode();  //3 letter currency abbreviation (USD)
        		$p['insurance_value'] = $pkg->getInsuranceValue();  //limited to 99999999.99 with up to 2 digits after
        	} else if ($pkg->getInvoiceTotal() >= 1000) {  //force declared value on packages
                $p['insurance_type'] = '01';
                $p['insurance_currencycode'] = 'USD';
                $p['insurance_value'] = sprintf('%01.2f', $pkg->getInvoiceTotal());
            }
        	//if release without signature requested
        	if($pkg->getReleaseWithoutSignature())  {
        		$p['release_without_signature'] = 1;  //if non-null driver may release package without a signature, only valid in US & Puerto Rico
        	}
        	//if verbal confirmation requested
        	if($pkg->getVerballyConfirm())  {
        		$p['verbal_name'] = substr($store->getConfig('carriers/ups/shipper_attention'), 0, 35);  //35 char, contact name to notify on delivery
        		$p['verbal_phone'] = substr($store->getConfig('carriers/ups/shipper_phone'), 0, 15);  //required for international destinations
        	}
        	$pkgs[] = $p;

        	//add the package items to the invoice total
        	$invoicetotal += $pkg->getInvoiceTotal();
        	if($p['package_description'] == 'UPS Letter')  {  //if any of packages is UPS Letter, don't use the invoice total
        		$invoicetotalpkgtype = false;
        	}
        }

        $ship = explode('_', $order->getShippingMethod());
        $servicecode = Mage::getModel('usa/shipping_carrier_ups')->getCode('method_code', $ship[1]);
        if(empty($servicecode))  {
        	$servicecode = $ship[1];
        }
		$params = array(
			'order_id' => $orderid,
			'address_validation' =>  'nonvalidate',  //could also be validate TODO: implement this on the frontend when address is entered
			'shipment_description' => 'Order_id '.$orderid,  //35 chars, required for some international, optional
			'shipper_name' => substr($order->getStore()->getWebsite()->getName(), 0, 35),  //35 chars
			'shipper_attention' => substr($store->getConfig('carriers/ups/shipper_attention'), 0, 35),  //35 chars required for international and next day AM
			'shipper_number' => $store->getConfig('carriers/ups/ups_account_number'),  //6 digit UPS account number
			'shipper_phone' => $store->getConfig('shipping/origin/phone'),  //15 chars, digits only, required for international, optional
			'shipper_email' => $store->getConfig('trans_email/ident_shipping/email'),  //50 chars, optional
			'shipper_addr1' => $store->getConfig('shipping/origin/address1'),  //35 chars
			'shipper_addr2' => $store->getConfig('shipping/origin/address2'),  //35 chars
			'shipper_addr3' => $store->getConfig('shipping/origin/address3'),  //35 chars
			'shipper_city' => $store->getConfig('shipping/origin/city'),  //30 chars
			'shipper_state' => Mage::getModel('directory/region')->load($store->getConfig('shipping/origin/region_id'))->getCode(),  //2-5 chars, required for US, Mexico, and Canada (for Ireland use 5 digit county abbreviation)
			'shipper_postalcode' => $store->getConfig('shipping/origin/postcode'),  //9 chars , required for US, Canada, Puerto Rico, may include - with 9 digits
			'shipper_country' => $store->getConfig('shipping/origin/country_id'),  //2 digit ISO code

			'shipto_name' => substr($shipaddress->getName(), 0, 35),  //35 chars
			'shipto_attention' => substr($shipaddress->getName(), 0, 35),  //35 chars, required for international and next day AM
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
			'service_description' => $this->getCode('method', $ship[1]),  //UPS shipment service description, optional
		//TODO: Make this retreive currency from admin config
			'invoice_line_total_currency_code' => 'USD',
			'invoice_line_total_value' => round($invoicetotal),
			'invoice_total_pkg_type' => $invoicetotalpkgtype,

			'packages' => $pkgs,  //package information
			'label_printcode' => 'GIF',  //Print method, valid values are GIF, EPL, ZPL, and SPL  TODO: Add to admin config
			'label_agent' => 'Mozilla/4.5',  //HTTPUserAgent Supported user agent values are 'Mozilla/7.0', 'Mozilla/6.0', 'Mozilla/5.0', 'Mozilla/4.9', 'Mozilla/4.8', 'Mozilla/4.7', 'Mozilla/4.6', 'Mozilla/4.5', 'Mozilla/4.4', 'Mozilla/4.3', 'Mozilla/4.2', 'Mozilla/4.1', 'Mozilla/4.09', 'Mozilla/4.08', 'Mozilla/4.07', 'Mozilla/4.06', 'Mozilla/4.05', 'MSIE 4.5', 'MSIE 5.0', 'MSIE 5.5', 'MSIE 6.0', and 'MSIE 7.0';
			'label_imagecode' => 'GIF', //Image format, valid values are GIF, EPL, ZPL, and SPL

			'thirdparty_number' => $store->getConfig('carriers/ups/third_party_ups_account_number'),  //6 digit UPS account number
			'thirdparty_postalcode' => $store->getConfig('carriers/ups/third_party_postcode'),  //required for US, Canada, Puerto Rico, 9 digits plus -
			'thirdparty_country' => $store->getConfig('carriers/ups/third_party_country'),  //2 digit ISO code

			'destination_type' => $store->getConfig('carriers/ups/dest_type'),  //RES=Residential, COM=Commercial
		);

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
		    <EmailAddress><![CDATA[{$params['shipper_email']}]]></EmailAddress>
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
		    <AttentionName><![CDATA[{$params['shipto_attention']}]]></AttentionName>
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
		if($params['destination_type'] == 'RES')  {
			$xmlRequest .= <<< XMLRequest

		         <ResidentialAddress />
XMLRequest;
		}

		$xmlRequest .= <<< XMLRequest

		    </Address>
		</ShipTo>
XMLRequest;
		//if negotiated rate should be retrieved
		if($params['negotiated_rate'])  {
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
		if($params['invoice_total_pkg_type'] && ($params['shipper_country'] == 'US') && (($params['shipto_country'] == 'CA') ||
		  ($params['shipto_country'] == 'US' && $params['shipto_state'] == 'PR')))  {
		  	$xmlRequest .= <<< XMLRequest

		<InvoiceLineTotal>
			<CurrencyCode>{$params['invoice_line_total_currency_code']}</CurrencyCode>
			<MonetaryValue>{$params['invoice_line_total_value']}</MonetaryValue>
		</InvoiceLineTotal>
XMLRequest;
		}

		//Determine the payment method to be used
		//third party account (different from shipper)
		if(!empty($params['thirdparty_number']))  {
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
		foreach ($params['packages'] as $pkg)  {
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
		if(!empty($pkg['reference_code']))  {
			$xmlRequest .= <<< XMLRequest
	        <ReferenceNumber>
	              <Code>{$pkg['reference_code']}</Code>
	              <Value>{$pkg['reference_value']}</Value>
	        </ReferenceNumber>
XMLRequest;
		}

			//add any applicable Service Options
			if(!empty($pkg['confirmation_type']) || !empty($pkg['insurance_currencycode']) || !empty($pkg['verbal_name']))  {
				$xmlRequest .=
'	        <PackageServiceOptions>';

				//if confirmation requested
				if(!empty($pkg['confirmation_type']))  {
					$xmlRequest .= '
				  <DeliveryConfirmation>
					   <DCISType>'.$pkg['confirmation_type'].'</DCISType>
					   <DCISNumber>'.$pkg['confirmation_number'].'</DCISNumber>
				  </DeliveryConfirmation>';
				}

				//if insurance requested
				if(!empty($pkg['insurance_type']))  {
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
				if(!empty($pkg['verbal_name']))  {
					$xmlRequest .= '
	              <VerbalConfirmation>
	              	   <ContactInfo>
	                   	  <Name><![CDATA['.$pkg['verbal_name'].']]></Name>
	                   	  <PhoneNumber><![CDATA['.$pkg['verbal_phone'].']]></PhoneNumber>
	                   </ContactInfo>
	              </VerbalConfirmation>';
				}

				//if release without signature is set
				if(!empty($pkg['release_without_signature']))  {
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
XMLRequest;

		//determine if the package needs InternationalForms
		if($params['shipto_country'] != $params['shipper_country'])  {
			$xmlRequest .= $this->_createInternationalFormsXml(INTERNATIONAL_FORM_TYPE_INVOICE, $orderid, $packages);
		}

		//close the document
		$xmlRequest .= '
</ShipmentConfirmRequest>';

//print_r($xmlRequest);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getConfigFlag('mode_xml'));
		$xmlResponse = curl_exec ($ch);

		//check for error
		if(curl_error($ch))  {
			throw new Exception(Mage::helper('ordership')->__('Error connecting to API:  ').curl_error($ch));
		}
		curl_close($ch);

		return $this->_parseXmlShipmentConfirmResponse($xmlResponse, $params['order_id']);
    }

    /**
     * Parses the XML from a ShipmentConfirmReponse document and returns the results
     *
     * @param string $xmlResponse The full ShipmentConfirmResponse XML document
     * @param string $transaction_reference The reference id supplied in the initial ShipmentConfirmRequest.
     * @return Mage_Shipping_Model_Shipment_Confirmation The ShipmentConfirmationResponse object
     */
	protected function _parseXmlShipmentConfirmResponse($xmlResponse, $transaction_reference)  {
		//create the result and set the raw response
    	$result = Mage::getModel('shipping/shipment_confirmation');

    	if(!$result->setRawResponse($xmlResponse))  {
    		return $result;
    	}

    	$success = (int)$result->getValueForXpath("//ShipmentConfirmResponse/Response/ResponseStatusCode/");
		if($success === 1)  {
			//get the transaction reference and make sure it matches what was sent
			$tref = $result->getValueForXpath("//ShipmentConfirmResponse/Response/TransactionReference/CustomerContext/");
			//if the transaction reference does not match
			if($tref != $transaction_reference)  {
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
			$negtotal = $result->getValueForXpath("//ShipmentConfirmResponse/NegotitatedRates/NetSummaryCharges/GrandTotal/MonetaryValue/");
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
			if(empty($total) || empty($shipmentid) || empty($shipmentdigest))  {
				$errmsg = "Required parameter(s) not found in response: ";
				if(empty($total))  {
					$errmsg .= 'TotalCharges, ';
				}
				if(empty($shipmentid))  {
					$errmsg .= 'ShipmentIdentificationNumber, ';
				}
				if(empty($shipmentdigest))  {
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
     * @param string $customercontext The magento defined identifier for the request (order_id)
     * @param string $shipmentdigest The UPS generated digest to identify the request
     * @return unknown
     */
    protected function _sendXmlShipmentAcceptRequest($url, $customercontext, $shipmentdigest)  {
    	$order = Mage::getModel('sales/order')->loadByIncrementId($this->_shiprequest->getOrderId());

    	if(empty($url))  {
    		$url = rtrim($order->getStore()->getConfig('carriers/ups/shipping_xml_url'), '/').'/ShipAccept';
    	}

    	//start with the access request
    	$xmlRequest = $this->_xmlAccessRequest;
    	//add the rest of the XML
		$xmlRequest .= <<< XMLRequest

<?xml version="1.0"?>
<ShipmentAcceptRequest>
    <Request>
         <TransactionReference>
              <CustomerContext>{$customercontext}</CustomerContext>
              <XpciVersion>1.0001</XpciVersion>
         </TransactionReference>
         <RequestAction>ShipAccept</RequestAction>
    </Request>
    <ShipmentDigest><![CDATA[{$shipmentdigest}]]></ShipmentDigest>
</ShipmentAcceptRequest>
XMLRequest;

        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getConfigFlag('mode_xml'));
		$xmlResponse = curl_exec ($ch);

		//check for error
		if(curl_error($ch))  {
			throw new Exception(Mage::helper('ordership')->__('Error connecting to API:  ').curl_error($ch));
		}
		curl_close($ch);

		return $this->_parseXmlShipmentAcceptResponse($xmlResponse, $customercontext);
    }

    /**
     * Parses the XML from a ShipmentAcceptReponse document and returns the results
     *
     * @param string $xmlResponse The full ShipmentConfirmResponse XML document
     * @param string $transaction_reference The reference id supplied in the initial ShipmentConfirmRequest.
     * @return Mage_Shipping_Model_Shipment_Result The ShipmentAcceptResponse object
     */
    protected function _parseXmlShipmentAcceptResponse($xmlResponse, $transaction_reference)  {
    	//create the result and set the raw response
    	$result = Mage::getModel('shipping/shipment_confirmation');
    	if(!$result->setRawResponse($xmlResponse))  {
    		return $result;
    	}

    	$success = (int)$result->getValueForXpath("//ShipmentAcceptResponse/Response/ResponseStatusCode/");
		if($success === 1)  {
			//get the transaction reference and make sure it matches what was sent
			$tref = $result->getValueForXpath("//ShipmentAcceptResponse/Response/TransactionReference/CustomerContext/");
			//if the transaction reference does not match
			if($tref != $transaction_reference)  {
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
			$negtotal = $result->getValueForXpath("//ShipmentAcceptResponse/ShipmentResults/NegotitatedRates/NetSummaryCharges/GrandTotal/MonetaryValue/");
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
            $intl_doc = $result->getXpath('//ShipmentAcceptResponse/ShipmentResults/Form/Image/GraphicImage');;
		    $result->setIntlDoc($intl_doc);

			//get all packages
			$xmlpackages = $result->getXpath("//ShipmentAcceptResponse/ShipmentResults/PackageResults");
			$packages = array();
            if ($xmlpackages) {
            	$i = 1;
                foreach ($xmlpackages as $package) {
                	//get the package info
                	//tracking number
                	$tracking = (string)$package->TrackingNumber;
                	//service option charges
                	$service_option_currency = null;
                	if(isset($package->ServiceOptionCharges->CurrencyCode))  {
                		$service_option_currency = (string)$tracking->ServiceOptionCharges->CurrencyCode;
                	}
                	$service_option_charge = null;
                	if(isset($package->ServiceOptionCharges->MonetaryValue))  {
                		$service_option_charge = (string)$tracking->ServiceOptionCharges->MonetaryValue;
                	}
                	//label images
                	$label_image = null;
                	$label_image_format = null;
                	$html_image = null;
                	if(isset($package->LabelImage))  {
                		$label_image_format = (string)$package->LabelImage->LabelImageFormat->Code;
                		$label_image = (string)$package->LabelImage->GraphicImage;  //Base64 encoded
                		if(isset($package->LabelImage->HTMLImage))  {
                			$html_image = $package->LabelImage->HTMLImage;  //Base64 encoded GIF
                		}
                	}

                	//add data to packages array
                	$pkg = array(
                		'package_number' => $i,
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
                	$i++;
                }
            }
			$result->setPackages($packages);

			//make sure all required params are present
			if(empty($total) || empty($shipmentid) || empty($packages))  {
				$errmsg = "Required parameter(s) not found in response: ";
				if(empty($total))  {
					$errmsg .= 'TotalCharges, ';
				}
				if(empty($shipmentid))  {
					$errmsg .= 'ShipmentIdentificationNumber, ';
				}
				if(empty($packages))  {
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

    /**
     * This function returns the XML needed for an InternationalForms document based on the params passed in.
     *
     * @param mixed $formtype Can be a single int or an array. Should be one or more of the following:
     * 		INTERNATIONAL_FORM_TYPE_INVOICE = 1
     * 		INTERNATIONAL_FORM_TYPE_SHIPPERS_EXPORT_DECLARATION = 2
     * 		INTERNATIONAL_FORM_TYPE_CERTIFICATE_OF_ORIGIN = 3
     * 		INTERNATIONAL_FORM_TYPE_NAFTA_CERTIFICATE_OF_ORIGIN = 4
     * @param int $orderid
     * @param array $packages An array of Zenprint_Shipping_Model_Shipment_Package objects representing the packages that the form(s) should be
     * 	generated for.
     * @return string The InternationalForms XML document or on error an exception is thrown and false returned.
     */
    protected function _createInternationalFormsXml($formtype, $orderid, $packages)  {
    	if(empty($formtype) || empty($orderid) || empty($packages))  {
    		throw Mage::exception('Mage_Shipping', Mage::helper('usa')->__('Invalid InternationalForms data.'));
    		return false;
    	}

    	//get some order info
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
    	$store = $order->getStore();
    	$shipaddress = $order->getShippingAddress();
    	$orderdate = $order->getCreatedAt();
    	$srccountry = $store->getConfig('shipping/origin/country_id');
    	$freightcost = $order->getShippingAmount();

    	//destination data
    	$dconame = $shipaddress->getName();
    	$daddress1 = $shipaddress->getStreet(1);
    	$daddress2 = $shipaddress->getStreet(2);
    	$daddress3 = $shipaddress->getStreet(3);
    	$dcity = $shipaddress->getCity();
    	$dstate = $shipaddress->getRegionCode();
    	$dzip = $shipaddress->getPostCode();
    	$dcountry = $shipaddress->getCountryId();

    	//make sure the form type is an array
    	if(!is_array($formtype))  {
    		$formtype = array($formtype);
    	}

    	$xmlval = '
    <InternationalForms>';

    	//set the types
    	foreach ($formtype as $typ)  {
    		$xmlval .= '
		<FormType>'.$typ.'</FormType>';
    	}

    	//if filing SED
    	if(in_array(INTERNATIONAL_FORM_TYPE_SHIPPERS_EXPORT_DECLARATION, $formtype))  {
    		$xmlval .= <<< XMLVal

    	<SEDFilingOption>01</SEDFilingOption>
XMLVal;
    	}

    	//if contacts required
    	if(in_array(INTERNATIONAL_FORM_TYPE_NAFTA_CERTIFICATE_OF_ORIGIN, $formtype) || in_array(INTERNATIONAL_FORM_TYPE_SHIPPERS_EXPORT_DECLARATION, $formtype))  {
    		$xmlval .= '
    	<Contacts>';

    		//ultimate consignee needed for SED
			if(in_array(INTERNATIONAL_FORM_TYPE_SHIPPERS_EXPORT_DECLARATION, $formtype))  {
				$xmlval .= <<< XMLVal

			<UltimateConsignee>
				<CompanyName>{$dconame}</CompanyName>
				<Address>
					<AddressLine1>{$daddress1}</AddressLine1>
					<AddressLine2>{$daddress2}</AddressLine2>
					<AddressLine3>{$daddress3}</AddressLine3>
					<City>{$dcity}</City>
					<StateProvinceCode>{$dstate}</StateProvinceCode>
					<PostalCode>{$dzip}</PostalCode>
					<CountryCode>{$dcountry}</CountryCode>
				</Address>
			</UltimateConsignee>
XMLVal;
			}

			//needed for NAFTA CO
			if(in_array(INTERNATIONAL_FORM_TYPE_NAFTA_CERTIFICATE_OF_ORIGIN, $formtype))  {
				$xmlval .= '
			<Producer>
				<Option>02</Option>
			</Producer>';
			}
			//close contacts
    		$xmlval .= '
    	</Contacts>';
    	}

    	//add the products
    	foreach ($packages as $package)  {
	    	foreach ($package->getItems() as $id => $qty)  {
	    		$oitem = Mage::getModel('sales/order_item')->load($id);
	    		$descr = $oitem->getName();
	    		if($oitem->getDescription() != '')  {
	    			$descr .= ' - '.$oitem->getDescription();
	    		}
	    		$descr .= ', Qty: '.$qty;

	    		$xmlval .= '
	    <Product>
	    	<Description>'.$descr.'</Description>
	    	<OriginCountryCode>'.$srccountry.'</OriginCountryCode>';

    		//if invoice or NAFTA CO
    		if(in_array(INTERNATIONAL_FORM_TYPE_INVOICE, $formtype) || in_array(INTERNATIONAL_FORM_TYPE_NAFTA_CERTIFICATE_OF_ORIGIN, $formtype))  {
    			//TODO: Implement commodity code for NAFTA CO
    			$xmlval .= '
			<CommodityCode></CommodityCode>';
    			$xmlval .= '
    		<OriginCountryCode>'.$srccountry.'</OriginCountryCode>';
    		}

    		//TODO: There is some more crap here needed for the NAFTA CO, and CO ignoring for now

    		$xmlval .= '
    	</Product>';
	    	}
    	}

    	if(in_array(INTERNATIONAL_FORM_TYPE_INVOICE, $formtype))  {
    		//TODO: Figure out proper TermsOfShipment
	    	$xmlval .= '
	    <InvoiceNumber>'.$orderid.'</InvoiceNumber>
	    <InvoiceDate>'.$orderdate.'</InvoiceDate>
	    <PurchaseOrderNumber>'.$orderid.'</PurchaseOrderNumber>
	    <TermsOfShipment>DDP</TermsOfShipment>
	    <ReasonForExport>SALE</ReasonForExport>
		<FreightCharges>
			<MonetaryValue>'.$freightcost.'</MonetaryValue>
		</FreightCharges>';
    	}

    	$xmlval .= '
	</InternationalForms>';

		return $xmlval;
    }

    /**
     * Generates and executes the actual XML requests to forward a shipment, which consists of 4 separate steps:
     *
     * 	1. ShipmentConfirmRequest - submission of request for shipment containing proposed shipment information
     * 	2. ShipmentConfirmResponse - data sent back containing rate information for the proposed shipment
     * 	3. ShipmentAcceptRequest - submission indicating acceptance of proposed rate for shipment
     * 	4. ShipmentAcceptResponse - data sent back containing tracking info and shipping label
     *
     * @return array An array of Mage_Shipping_Model_Shipment_Package objects. An exception will be thrown on error.
     */
    protected function _createXmlShipment()  {
    	$orderid = $this->_shiprequest->getOrderId();
    	$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
    	$store = $order->getStore();

    	//get the params
    	$url = $store->getConfig('carriers/ups/shipping_xml_url');
		//make sure the $url does not have ShipConfirm or ShipAccept on the end
		$url = (rtrim($url, 'ShipConfirm'));
		$url = (rtrim($url, 'ShipAccept'));
		$url = (rtrim($url, '/'));


    	$packages = $this->_shiprequest->getPackages();

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

	    	//ShipmentConfirmRequest
	    	$ship_confirm_response = $this->_sendXmlShipmentConfirmRequest($orderid, array($reqpackage), $url.'/ShipConfirm');

	    	//TODO: Make sure there was no error with the ShipmentConfirmRequest
	    	//this is currently accomplished using exceptions

	    	//ShipmentAcceptRequest
	    	$ship_accept_response = $this->_sendXmlShipmentAcceptRequest($url.'/ShipAccept', $orderid, $ship_confirm_response->getShipmentDigest());

	    	//TODO: Make sure there was no error with the ShipmentAcceptRequest
	    	//this is currently accomplished using exceptions

	    	//store the results of the request if it was successful
			foreach ($ship_accept_response->getPackages() as $respackage)  {
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
					->setCarrierCode('ups')
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
		    		->setCarrier('ups')
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
		    		->setDateShipped(date('Y-m-d H:i:s'))
		    		->setInsDoc($ship_accept_response->getInsDoc())
		    		->setIntlDoc($ship_accept_response->getIntlDoc())
		    		->save();

		    	$retval[] = $pkg;
	    	}
    	}

    	return $retval;
    }

}
