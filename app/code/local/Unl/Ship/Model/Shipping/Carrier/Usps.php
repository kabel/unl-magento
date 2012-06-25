<?php

class Unl_Ship_Model_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps
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

    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('usa')->__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
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
}
