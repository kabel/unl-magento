<?php

class Unl_Ship_Model_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps
    implements Unl_Ship_Model_Shipping_Carrier_VoidInterface
{
    protected $_lastVoidResult;

    public function getCode($type, $code='')
    {
        $codes = array(
            'service'=>array(
                'FIRST CLASS' => Mage::helper('usa')->__('First-Class Mail'),
                'EXPRESS'     => Mage::helper('usa')->__('Priority Mail Express'),
                'PARCEL'      => Mage::helper('usa')->__('Standard Post'),
            ),

            'service_to_code'=>array(
                'First-Class Mail Postcards'                 => 'FIRST CLASS',
                'First-Class Mail Large Postcards'           => 'FIRST CLASS',
                'First-Class Package International Service'  => 'FIRST CLASS',
                'Standard Post'                              => 'PARCEL',

                'Express Mail Sunday/Holiday Delivery'                           => 'EXPRESS',
                'Express Mail Flat Rate Boxes'                                   => 'EXPRESS',
                'Express Mail Flat Rate Boxes Hold For Pickup'                   => 'EXPRESS',
                'Express Mail Sunday/Holiday Delivery Flat-Rate Boxes'           => 'EXPRESS',
                'Express Mail Sunday/Holiday Delivery Flat-Rate Envelope'        => 'EXPRESS',
                'Express Mail Legal Flat Rate Envelope'                          => 'EXPRESS',
                'Express Mail Legal Flat Rate Envelope Hold For Pickup'          => 'EXPRESS',
                'Express Mail Sunday/Holiday Delivery Legal Flat Rate Envelope'  => 'EXPRESS',
                'Express Mail Padded Flat Rate Envelope'                         => 'EXPRESS',
                'Express Mail Padded Flat Rate Envelope Hold For Pickup'         => 'EXPRESS',
                'Express Mail Sunday/Holiday Delivery Padded Flat Rate Envelope' => 'EXPRESS',
                'Express Mail International Flat Rate Boxes'                     => 'EXPRESS',
                'Express Mail International Legal Flat Rate Envelope'            => 'EXPRESS',
                'Express Mail International Padded Flat Rate Envelope'           => 'EXPRESS',
                'Priority Mail Legal Flat Rate Envelope'                         => 'PRIORITY',
                'Priority Mail Padded Flat Rate Envelope'                        => 'PRIORITY',
                'Priority Mail Gift Card Flat Rate Envelope'                     => 'PRIORITY',
                'Priority Mail Small Flat Rate Envelope'                         => 'PRIORITY',
                'Priority Mail Window Flat Rate Envelope'                        => 'PRIORITY',
                'Priority Mail International DVD Flat Rate priced box'           => 'PRIORITY',
                'Priority Mail International Large Video Flat Rate priced box'   => 'PRIORITY',
                'Priority Mail International Legal Flat Rate Envelope'           => 'PRIORITY',
                'Priority Mail International Padded Flat Rate Envelope'          => 'PRIORITY',
                'Priority Mail International Gift Card Flat Rate Envelope'       => 'PRIORITY',
                'Priority Mail International Small Flat Rate Envelope'           => 'PRIORITY',
                'Priority Mail International Window Flat Rate Envelope'          => 'PRIORITY',
                // added by USPS 7/28/2013
                'Priority Mail 1-Day'                                            => 'PRIORITY',
                'Priority Mail 1-Day Flat Rate Envelope'                         => 'PRIORITY',
                'Priority Mail 1-Day Gift Card Flat Rate Envelope'               => 'PRIORITY',
                'Priority Mail 1-Day Large Flat Rate Box'                        => 'PRIORITY',
                'Priority Mail 1-Day Legal Flat Rate Envelope'                   => 'PRIORITY',
                'Priority Mail 1-Day Medium Flat Rate Box'                       => 'PRIORITY',
                'Priority Mail 1-Day Padded Flat Rate Envelope'                  => 'PRIORITY',
                'Priority Mail 1-Day Small Flat Rate Box'                        => 'PRIORITY',
                'Priority Mail 1-Day Small Flat Rate Envelope'                   => 'PRIORITY',
                'Priority Mail 1-Day Window Flat Rate Envelope'                  => 'PRIORITY',
                'Priority Mail 2-Day'                                            => 'PRIORITY',
                'Priority Mail 2-Day Flat Rate Envelope'                         => 'PRIORITY',
                'Priority Mail 2-Day Gift Card Flat Rate Envelope'               => 'PRIORITY',
                'Priority Mail 2-Day Large Flat Rate Box'                        => 'PRIORITY',
                'Priority Mail 2-Day Legal Flat Rate Envelope'                   => 'PRIORITY',
                'Priority Mail 2-Day Medium Flat Rate Box'                       => 'PRIORITY',
                'Priority Mail 2-Day Padded Flat Rate Envelope'                  => 'PRIORITY',
                'Priority Mail 2-Day Small Flat Rate Box'                        => 'PRIORITY',
                'Priority Mail 2-Day Small Flat Rate Envelope'                   => 'PRIORITY',
                'Priority Mail 2-Day Window Flat Rate Envelope'                  => 'PRIORITY',
                'Priority Mail Express 1-Day'                                                   => 'EXPRESS',
                'Priority Mail Express 1-Day Flat Rate Boxes'                                   => 'EXPRESS',
                'Priority Mail Express 1-Day Flat Rate Boxes Hold For Pickup'                   => 'EXPRESS',
                'Priority Mail Express 1-Day Flat Rate Envelope'                                => 'EXPRESS',
                'Priority Mail Express 1-Day Flat Rate Envelope Hold For Pickup'                => 'EXPRESS',
                'Priority Mail Express 1-Day Hold For Pickup'                                   => 'EXPRESS',
                'Priority Mail Express 1-Day Legal Flat Rate Envelope'                          => 'EXPRESS',
                'Priority Mail Express 1-Day Legal Flat Rate Envelope Hold For Pickup'          => 'EXPRESS',
                'Priority Mail Express 1-Day Padded Flat Rate Envelope'                         => 'EXPRESS',
                'Priority Mail Express 1-Day Padded Flat Rate Envelope Hold For Pickup'         => 'EXPRESS',
                'Priority Mail Express 1-Day Sunday/Holiday Delivery'                           => 'EXPRESS',
                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Boxes'           => 'EXPRESS',
                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Envelope'        => 'EXPRESS',
                'Priority Mail Express 1-Day Sunday/Holiday Delivery Legal Flat Rate Envelope'  => 'EXPRESS',
                'Priority Mail Express 1-Day Sunday/Holiday Delivery Padded Flat Rate Envelope' => 'EXPRESS',
                'Priority Mail Express 2-Day'                                                   => 'EXPRESS',
                'Priority Mail Express 2-Day Flat Rate Boxes'                                   => 'EXPRESS',
                'Priority Mail Express 2-Day Flat Rate Boxes Hold For Pickup'                   => 'EXPRESS',
                'Priority Mail Express 2-Day Flat Rate Envelope'                                => 'EXPRESS',
                'Priority Mail Express 2-Day Flat Rate Envelope Hold For Pickup'                => 'EXPRESS',
                'Priority Mail Express 2-Day Hold For Pickup'                                   => 'EXPRESS',
                'Priority Mail Express 2-Day Legal Flat Rate Envelope'                          => 'EXPRESS',
                'Priority Mail Express 2-Day Legal Flat Rate Envelope Hold For Pickup'          => 'EXPRESS',
                'Priority Mail Express 2-Day Padded Flat Rate Envelope'                         => 'EXPRESS',
                'Priority Mail Express 2-Day Padded Flat Rate Envelope Hold For Pickup'         => 'EXPRESS',
                'Priority Mail Express International'                                           => 'EXPRESS',
                'Priority Mail Express International Flat Rate Boxes'                           => 'EXPRESS',
                'Priority Mail Express International Flat Rate Envelope'                        => 'EXPRESS',
                'Priority Mail Express International Legal Flat Rate Envelope'                  => 'EXPRESS',
                'Priority Mail Express International Padded Flat Rate Envelope'                 => 'EXPRESS',
            ),

            'containers_filter' => array(
                array(
                    'containers' => array('VARIABLE'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express 1-Day Flat Rate Boxes',
                                'Priority Mail Express 1-Day Flat Rate Boxes Hold For Pickup',
                                'Priority Mail Express 1-Day Flat Rate Envelope',
                                'Priority Mail Express 1-Day Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Hold For Pickup',
                                'Priority Mail Express 1-Day Legal Flat Rate Envelope',
                                'Priority Mail Express 1-Day Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Padded Flat Rate Envelope',
                                'Priority Mail Express 1-Day Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Boxes',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Envelope',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Legal Flat Rate Envelope',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Padded Flat Rate Envelope',
                                'Priority Mail Express 2-Day Flat Rate Boxes',
                                'Priority Mail Express 2-Day Flat Rate Boxes Hold For Pickup',
                                'Priority Mail Express 2-Day Flat Rate Envelope',
                                'Priority Mail Express 2-Day Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 2-Day Hold For Pickup',
                                'Priority Mail Express 2-Day Legal Flat Rate Envelope',
                                'Priority Mail Express 2-Day Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 2-Day Padded Flat Rate Envelope',
                                'Priority Mail Express 2-Day Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail 1-Day Flat Rate Envelope',
                                'Priority Mail 1-Day Gift Card Flat Rate Envelope',
                                'Priority Mail 1-Day Large Flat Rate Box',
                                'Priority Mail 1-Day Legal Flat Rate Envelope',
                                'Priority Mail 1-Day Medium Flat Rate Box',
                                'Priority Mail 1-Day Padded Flat Rate Envelope',
                                'Priority Mail 1-Day Small Flat Rate Box',
                                'Priority Mail 1-Day Small Flat Rate Envelope',
                                'Priority Mail 1-Day Window Flat Rate Envelope',
                                'Priority Mail 2-Day Flat Rate Envelope',
                                'Priority Mail 2-Day Large Flat Rate Box',
                                'Priority Mail 2-Day Legal Flat Rate Envelope',
                                'Priority Mail 2-Day Medium Flat Rate Box',
                                'Priority Mail 2-Day Padded Flat Rate Envelope',
                                'Priority Mail 2-Day Small Flat Rate Box',
                                'Priority Mail 2-Day Small Flat Rate Envelope',
                                'Priority Mail 2-Day Window Flat Rate Envelope',
                                'Priority Mail Express 1-Day',
                                'Priority Mail Express 2-Day',
                                'Priority Mail 1-Day',
                                'Priority Mail 2-Day',
                                'Standard Post',
                                'Media Mail',
                                'First-Class Mail Large Envelope',
                                'First-Class Mail Parcel',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail Express International Flat Rate Boxes',
                                'Priority Mail Express International Flat Rate Envelope',
                                'Priority Mail Express International Legal Flat Rate Envelope',
                                'Priority Mail Express International Padded Flat Rate Envelope',
                                'Priority Mail International DVD Flat Rate priced box',
                                'Priority Mail International Flat Rate Envelope',
                                'Priority Mail International Gift Card Flat Rate Envelope',
                                'Priority Mail International Large Flat Rate Box',
                                'Priority Mail International Large Video Flat Rate priced box',
                                'Priority Mail International Legal Flat Rate Envelope',
                                'Priority Mail International Medium Flat Rate Box',
                                'Priority Mail International Padded Flat Rate Envelope',
                                'Priority Mail International Small Flat Rate Box',
                                'Priority Mail International Small Flat Rate Envelope',
                                'Priority Mail International Window Flat Rate Envelope',
                                'USPS GXG Envelopes',
                                'Priority Mail Express International',
                                'Priority Mail International',
                                'First-Class Mail International Large Envelope',
                                'First-Class Package International Service',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FLAT RATE BOX'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express 1-Day Flat Rate Boxes',
                                'Priority Mail Express 1-Day Flat Rate Boxes Hold For Pickup',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Boxes',
                                'Priority Mail Express 2-Day Flat Rate Boxes',
                                'Priority Mail Express 2-Day Flat Rate Boxes Hold For Pickup',
                                'Priority Mail 1-Day Large Flat Rate Box',
                                'Priority Mail 1-Day Medium Flat Rate Box',
                                'Priority Mail 1-Day Small Flat Rate Box',
                                'Priority Mail 2-Day Large Flat Rate Box',
                                'Priority Mail 2-Day Medium Flat Rate Box',
                                'Priority Mail 2-Day Small Flat Rate Box',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail Express International Flat Rate Boxes',
                                'Priority Mail International DVD Flat Rate priced box',
                                'Priority Mail International Large Flat Rate Box',
                                'Priority Mail International Large Video Flat Rate priced box',
                                'Priority Mail International Medium Flat Rate Box',
                                'Priority Mail International Small Flat Rate Box',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('FLAT RATE ENVELOPE'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express 1-Day Flat Rate Envelope',
                                'Priority Mail Express 1-Day Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Legal Flat Rate Envelope',
                                'Priority Mail Express 1-Day Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Padded Flat Rate Envelope',
                                'Priority Mail Express 1-Day Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Flat Rate Envelope',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Legal Flat Rate Envelope',
                                'Priority Mail Express 1-Day Sunday/Holiday Delivery Padded Flat Rate Envelope',
                                'Priority Mail Express 2-Day Flat Rate Envelope',
                                'Priority Mail Express 2-Day Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 2-Day Legal Flat Rate Envelope',
                                'Priority Mail Express 2-Day Legal Flat Rate Envelope Hold For Pickup',
                                'Priority Mail Express 2-Day Padded Flat Rate Envelope',
                                'Priority Mail Express 2-Day Padded Flat Rate Envelope Hold For Pickup',
                                'Priority Mail 1-Day Flat Rate Envelope',
                                'Priority Mail 1-Day Gift Card Flat Rate Envelope',
                                'Priority Mail 1-Day Legal Flat Rate Envelope',
                                'Priority Mail 1-Day Padded Flat Rate Envelope',
                                'Priority Mail 1-Day Small Flat Rate Envelope',
                                'Priority Mail 1-Day Window Flat Rate Envelope',
                                'Priority Mail 2-Day Flat Rate Envelope',
                                'Priority Mail 2-Day Gift Card Flat Rate Envelope',
                                'Priority Mail 2-Day Legal Flat Rate Envelope',
                                'Priority Mail 2-Day Padded Flat Rate Envelope',
                                'Priority Mail 2-Day Small Flat Rate Envelope',
                                'Priority Mail 2-Day Window Flat Rate Envelope',
                            )
                        ),
                        'from_us' => array(
                            'method' => array(
                                'Priority Mail Express International Flat Rate Envelope',
                                'Priority Mail Express International Legal Flat Rate Envelope',
                                'Priority Mail Express International Padded Flat Rate Envelope',
                                'Priority Mail International Flat Rate Envelope',
                                'Priority Mail International Gift Card Flat Rate Envelope',
                                'Priority Mail International Legal Flat Rate Envelope',
                                'Priority Mail International Padded Flat Rate Envelope',
                            )
                        )
                    )
                ),
                array(
                    'containers' => array('RECTANGULAR'),
                    'filters'    => array(
                        'within_us' => array(
                            'method' => array(
                                'Priority Mail Express 1-Day',
                                'Priority Mail Express 2-Day',
                                'Priority Mail 1-Day',
                                'Priority Mail 2-Day',
                                'Standard Post',
                                'Media Mail',
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
                                'Priority Mail Express 1-Day',
                                'Priority Mail Express 2-Day',
                                'Priority Mail 1-Day',
                                'Priority Mail 2-Day',
                                'Standard Post',
                                'Media Mail',
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
        $r->setWeightOunces(round(($weight-floor($weight)) * self::OUNCES_POUND, 1));

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

    /* Extends
     * @see Mage_Usa_Model_Shipping_Carrier_Abstract::_prepareShipmentRequest()
     * by escaping XML entities
     */
    protected function _prepareShipmentRequest(Varien_Object $request)
    {
        parent::_prepareShipmentRequest($request);
        foreach ($request->getData() as $key => $data) {
            if ((strpos($key, 'shipper') === 0 || strpos($key, 'recipient') === 0) && is_string($data)) {
                $request->setData($key, htmlspecialchars($data));
            }
        }
    }

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

    protected function _doShipmentRequest(Varien_Object $request)
    {
        if (!$this->getConfigData('endicia_enabled')) {
            return parent::_doShipmentRequest($request);
        }

        $this->_prepareShipmentRequest($request);
        $endicia = Mage::getSingleton('unl_ship/shipping_carrier_usps_endicia');

        return $endicia->doShipmentRequest($this, $request, $this->_isUSCountry($request->getRecipientAddressCountryCode()));
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
