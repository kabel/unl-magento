<?php

class Unl_Ship_Model_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps
    implements Unl_Ship_Model_Shipping_Carrier_VoidInterface
{
    protected $_lastVoidResult;

    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Usps::_parseXmlResponse()
     * to fix spacing issues
     */
    protected function _parseXmlResponse($response)
    {
        $costArr = array();
        $priceArr = array();
        if (strlen(trim($response)) > 0) {
            if (strpos(trim($response), '<?xml') === 0) {
                if (strpos($response, '<?xml version="1.0"?>') !== false) {
                    $response = str_replace(
                        '<?xml version="1.0"?>',
                        '<?xml version="1.0" encoding="ISO-8859-1"?>',
                        $response
                    );
                }

                $xml = simplexml_load_string($response);

                if (is_object($xml)) {
                    $r = $this->_rawRequest;
                    $allowedMethods = explode(',', $this->getConfigData('allowed_methods'));
                    $serviceCodeToActualNameMap = array();
                    /**
                     * US Rates
                     */
                    if ($this->_isUSCountry($r->getDestCountryId())) {
                        if (is_object($xml->Package) && is_object($xml->Package->Postage)) {
                            foreach ($xml->Package->Postage as $postage) {
                                $serviceName = $this->_filterServiceName((string)$postage->MailService);
                                $_serviceCode = $this->getCode('method_to_code', $serviceName);
                                $serviceCode = $_serviceCode ? $_serviceCode : (string)$postage->attributes()->CLASSID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$postage->Rate;
                                    $priceArr[$serviceCode] = $this->getMethodPrice(
                                        (string)$postage->Rate,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    }
                    /**
                     * International Rates
                     */
                    else {
                        if (is_object($xml->Package) && is_object($xml->Package->Service)) {
                            foreach ($xml->Package->Service as $service) {
                                $serviceName = $this->_filterServiceName((string)$service->SvcDescription);
                                $serviceCode = 'INT_' . (string)$service->attributes()->ID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$service->Postage;
                                    $priceArr[$serviceCode] = $this->getMethodPrice(
                                        (string)$service->Postage,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    }
                }
            }
        }

        $result = Mage::getModel('shipping/rate_result');
        if (empty($priceArr)) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = Mage::getModel('shipping/rate_result_method');
                $rate->setCarrier('usps');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle(
                    isset($serviceCodeToActualNameMap[$method])
                        ? $serviceCodeToActualNameMap[$method]
                        : $this->getCode('method', $method)
                );
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Usps::getCode()
     * to update container filters
     */
    public function getCode($type, $code = '')
    {
        $codes = array(
            'containers_filter' => array(
                array(
                    'containers' => array('VARIABLE'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'First-Class Mail Large Envelope',
                                'First-Class Mail Letter',
                                'First-Class Mail Parcel',
                                'First-Class Mail Postcards',
                                'Priority Mail',
                                'Priority Mail Express Hold For Pickup',
                                'Priority Mail Express',
                                'Standard Post',
                                'Media Mail',
                                'Library Mail',
                                'Priority Mail Express Flat Rate Envelope',
                                'First-Class Mail Large Postcards',
                                'Priority Mail Flat Rate Envelope',
                                'Priority Mail Medium Flat Rate Box',
                                'Priority Mail Large Flat Rate Box',
                                'Priority Mail Express Sunday/Holiday Delivery',
                                'Priority Mail Express Sunday/Holiday Delivery Flat Rate Envelope',
                                'Priority Mail Express Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Small Flat Rate Box',
                                'Priority Mail Padded Flat Rate Envelope',
                                'Priority Mail Express Legal Flat Rate Envelope',
                                'Priority Mail Express Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Legal Flat Rate Envelope',
                                'Priority Mail Hold For Pickup',
                                'Priority Mail Large Flat Rate Box Hold For Pickup',
                                'Priority Mail Medium Flat Rate Box Hold For Pickup',
                                'Priority Mail Small Flat Rate Box Hold For Pickup',
                                'Priority Mail Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Gift Card Flat Rate Envelope',
                                'Priority Mail Gift Card Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Window Flat Rate Envelope',
                                'Priority Mail Window Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Small Flat Rate Envelope',
                                'Priority Mail Small Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Legal Flat Rate Envelope',
                                'Priority Mail Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Regional Rate Box A',
                                'Priority Mail Regional Rate Box A Hold For Pickup',
                                'Priority Mail Regional Rate Box B',
                                'Priority Mail Regional Rate Box B Hold For Pickup',
                                'First-Class Package Service Hold For Pickup',
                                'Priority Mail Express Flat Rate Boxes',
                                'Priority Mail Express Flat Rate Boxes Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Flat Rate Boxes',
                                'Priority Mail Regional Rate Box C',
                                'Priority Mail Regional Rate Box C Hold For Pickup',
                                'First-Class Package Service',
                                'Priority Mail Express Padded Flat Rate Envelope',
                                'Priority Mail Express Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Padded Flat Rate Envelope',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail Express International',
                                'Priority Mail International',
                                'Global Express Guaranteed (GXG)',
                                'Global Express Guaranteed Non-Document Rectangular',
                                'Global Express Guaranteed Non-Document Non-Rectangular',
                                'Priority Mail International Flat Rate Envelope',
                                'Priority Mail International Medium Flat Rate Box',
                                'Priority Mail Express International Flat Rate Envelope',
                                'Priority Mail International Large Flat Rate Box',
                                'USPS GXG Envelopes',
                                'First-Class Mail International Letter',
                                'First-Class Mail International Large Envelope',
                                'First-Class Package International Service',
                                'Priority Mail International Small Flat Rate Box',
                                'Priority Express International Legal Flat Rate Envelope',
                                'Priority Mail International Gift Card Flat Rate Envelope',
                                'Priority Mail International Window Flat Rate Envelope',
                                'Priority Mail International Small Flat Rate Envelope',
                                'First-Class Mail International Postcard',
                                'Priority Mail International Legal Flat Rate Envelope',
                                'Priority Mail International Padded Flat Rate Envelope',
                                'Priority Mail International DVD Flat Rate priced box',
                                'Priority Mail International Large Video Flat Rate priced box',
                                'Priority Mail Express International Flat Rate Boxes',
                                'Priority Mail Express International Padded Flat Rate Envelope',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FLAT RATE BOX'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Medium Flat Rate Box',
                                'Priority Mail Large Flat Rate Box',
                                'Priority Mail Small Flat Rate Box',
                                'Priority Mail Large Flat Rate Box Hold For Pickup',
                                'Priority Mail Medium Flat Rate Box Hold For Pickup',
                                'Priority Mail Small Flat Rate Box Hold For Pickup',
                                'Priority Mail Regional Rate Box A',
                                'Priority Mail Regional Rate Box A Hold For Pickup',
                                'Priority Mail Regional Rate Box B',
                                'Priority Mail Regional Rate Box B Hold For Pickup',
                                'Priority Mail Express Flat Rate Boxes',
                                'Priority Mail Express Flat Rate Boxes Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Flat Rate Boxes',
                                'Priority Mail Regional Rate Box C',
                                'Priority Mail Regional Rate Box C Hold For Pickup',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail International Medium Flat Rate Box',
                                'Priority Mail International Large Flat Rate Box',
                                'Priority Mail International Small Flat Rate Box',
                                'Priority Mail International DVD Flat Rate priced box',
                                'Priority Mail International Large Video Flat Rate priced box',
                                'Priority Mail Express International Flat Rate Boxes',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FLAT RATE ENVELOPE'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'First-Class Mail Large Envelope',
                                'Priority Mail Express Flat Rate Envelope',
                                'Priority Mail Flat Rate Envelope',
                                'Priority Mail Express Sunday/Holiday Delivery Flat Rate Envelope',
                                'Priority Mail Express Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Padded Flat Rate Envelope',
                                'Priority Mail Express Legal Flat Rate Envelope',
                                'Priority Mail Express Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Legal Flat Rate Envelope',
                                'Priority Mail Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Gift Card Flat Rate Envelope',
                                'Priority Mail Gift Card Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Window Flat Rate Envelope',
                                'Priority Mail Window Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Small Flat Rate Envelope',
                                'Priority Mail Small Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Legal Flat Rate Envelope',
                                'Priority Mail Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express Padded Flat Rate Envelope',
                                'Priority Mail Express Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express Sunday/Holiday Delivery Padded Flat Rate Envelope',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail International Flat Rate Envelope',
                                'Priority Mail Express International Flat Rate Envelope',
                                'First-Class Mail International Large Envelope',
                                'Priority Express International Legal Flat Rate Envelope',
                                'Priority Mail International Gift Card Flat Rate Envelope',
                                'Priority Mail International Window Flat Rate Envelope',
                                'Priority Mail International Small Flat Rate Envelope',
                                'Priority Mail International Legal Flat Rate Envelope',
                                'Priority Mail International Padded Flat Rate Envelope',
                                'Priority Mail Express International Padded Flat Rate Envelope',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('RECTANGULAR'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express',
                                'Priority Mail',
                                'Standard Post',
                                'Media Mail',
                                'Library Mail',
                                'First-Class Package Service',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'USPS GXG Envelopes',
                                'Priority Mail Express International',
                                'Priority Mail International',
                                'First-Class Package International Service',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('NONRECTANGULAR'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express',
                                'Priority Mail',
                                'Standard Post',
                                'Media Mail',
                                'Library Mail',
                                'First-Class Package Service',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Global Express Guaranteed (GXG)',
                                'Priority Mail Express International',
                                'Priority Mail International',
                                'First-Class Package International Service',
                            )
                        )
                    )
                ),
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

    public function isVoidAvailable()
    {
        return (bool)$this->getConfigData('endicia_enabled');
    }

    /* Extends
     * @see Mage_Usa_Model_Shipping_Carrier_Usps::collectRates()
     * by not returning rates during pickup checkout flow
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $session = Mage::getSingleton('checkout/session');
        $quote = $session->getQuote();

        if (!$quote->getIsMultiShipping() && $session->getIsPickupFlow()) {
            return false;
        }

        return parent::collectRates($request);
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

        $defaultBox = false;
        // reset num box first before retrieve again
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

    /* Extends
     * @see Mage_Usa_Model_Shipping_Carrier_Usps::setRequest()
     * by changing the logic for calculating the rawRequest weight/boxes
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::setRequest($request);
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight(), $request->getAllItems());
        $r->setWeightPounds(floor($weight));
        $r->setWeightOunces(round(($weight - floor($weight)) * self::OUNCES_POUND, 1));

        $r->setOrigPostal(substr($r->getOrigPostal(), 0, 5));

        return $this;
    }

    public function proccessAdditionalValidation(Mage_Shipping_Model_Rate_Request $request)
    {
        $result = parent::proccessAdditionalValidation($request);

        if (false === $result || $result instanceof Mage_Shipping_Model_Rate_Result_Error) {
            return $result;
        }

        //Skip by item validation if there is no items in request
        if(!count($this->getAllItems($request))) {
            return $this;
        }

        $oldStore = $this->getStore();
        $stores = Mage::helper('unl_core')->getStoresFromItems($this->getAllItems($request));

        foreach ($stores as $store) {
            $this->setStore($store);
            if (!$this->isActive()) {
                $this->setStore($oldStore);
                return false;
            }
        }

        $this->setStore($oldStore);

        return $this;
    }

    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Abstract::_prepareShipmentRequest()
     * by escaping XML entities and removing non-digit chars from phone
     */
    protected function _prepareShipmentRequest(Varien_Object $request)
    {
        $phonePattern = '/[^\d]+/';
        $phoneNumber = $request->getShipperContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setShipperContactPhoneNumber($phoneNumber);
        $phoneNumber = $request->getRecipientContactPhoneNumber();
        $phoneNumber = preg_replace($phonePattern, '', $phoneNumber);
        $request->setRecipientContactPhoneNumber($phoneNumber);

        foreach ($request->getData() as $key => $data) {
            if ((strpos($key, 'shipper') === 0 || strpos($key, 'recipient') === 0) && is_string($data)) {
                $request->setData($key, htmlspecialchars($data));
            }
        }
    }

    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Abstract::requestToShipment()
     * to support UNL Package format
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('usa')->__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }

        $validResult = new Varien_Object();
        Mage::dispatchEvent('shipping_carrier_request_to_shipment', array(
            'request' => $request,
            'carrier' => $this,
            'result'  => $validResult,
        ));
        if ($validResult->getError()) {
            Mage::throwException($validResult->getError());
        }

        $data = array();
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new Varien_Object($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent(),
                    'package'         => $result->getPackage(),
                );
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new Varien_Object(array(
            'info'   => $data
        ));
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        } else {
            $shipment = $request->getOrderShipment();
            $pkgs = array();

            foreach ($response->getInfo() as $inf) {
                if ($inf['package']) {
                    $pkg = $inf['package'];
                    $pkg->setOrderId($shipment->getOrderId());
                    $pkg->setLabelImage($inf['label_content']);
                    $pkg->setTrackingNumber($inf['tracking_number']);
                    $pkg->setCarrier($this->getCarrierCode());
                    $pkg->setDateShipped(Mage::getSingleton('core/date')->gmtDate());

                    $pkgs[] = $pkg;
                }
            }

            if ($pkgs) {
                $shipment->setUnlPackages($pkgs);
            }
        }
        return $response;
    }

    /* Extends
     * @see Mage_Usa_Model_Shipping_Carrier_Usps::_doShipmentRequest()
     * to support Endicia processing
     */
    protected function _doShipmentRequest(Varien_Object $request)
    {
        if ($this->getConfigData('endicia_enabled')) {
            $this->_prepareShipmentRequest($request);
            $recipientUSCountry = $this->_isUSCountry($request->getRecipientAddressCountryCode());

            /* @var $endicia Unl_Ship_Model_Shipping_Carrier_Usps_Endicia */
            $endicia = Mage::getSingleton('unl_ship/shipping_carrier_usps_endicia');
            return $endicia->doShipmentRequest($this, $request, $recipientUSCountry);
        }

        return parent::_doShipmentRequest($request);
    }

    public function rollBack($data)
    {
        return $this->requestToVoid($data, true);
    }

    public function requestToVoid($data, $quiet = false)
    {
        if (!$this->getConfigData('endicia_enabled')) {
            return parent::rollBack($data);
        }

        $endicia = Mage::getSingleton('unl_ship/shipping_carrier_usps_endicia');
        foreach ($data as $info) {
            $this->_lastVoidResult = $result = $endicia->doRefundRequest($this, $info['tracking_number']);

            if ($result->hasErrors()) {
                if ($quiet) {
                    Mage::log('Tracking Number: ' . $info['tracking_number'], Zend_Log::INFO, 'unl_ship.log');
                    Mage::log($result->getErrors(), Zend_Log::WARN, 'unl_ship.log');
                    return false;
                } else {
                    Mage::throwException($result->getErrors());
                }
            }
        }

        return true;
    }

    public function getLastVoidResult()
    {
        return $this->_lastVoidResult;
    }
}
