<?php

class Unl_Ship_Model_Shipping_Carrier_Ups extends Mage_Usa_Model_Shipping_Carrier_Ups
{
    public function getConfigData($field)
    {
        if ($field == 'url') {
            if (parent::getConfigData('mode_xml')) {
                $this->_defaultUrls['ShipConfirm'] = 'https://onlinetools.ups.com/ups.app/xml/ShipConfirm';
                $this->_defaultUrls['ShipAccept']  = 'https://onlinetools.ups.com/ups.app/xml/ShipAccept';
            }
        }

        return parent::getConfigData($field);
    }

    /* Extends
     * @see Mage_Usa_Model_Shipping_Carrier_Ups::_getCorrectWeight()
     * by using new UPS recommedation to use next whole pound
     */
    protected function _getCorrectWeight($weight)
    {
        $weight = parent::_getCorrectWeight($weight);
        $weight = ceil($weight);

        return $weight;
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
     * @see Mage_Usa_Model_Shipping_Carrier_Ups::setRequest()
     * by changing the logic for calculating the rawRequest weight/boxes
     */
    public function setRequest(Mage_Shipping_Model_Rate_Request $request)
    {
        parent::setRequest($request);
        $r = $this->_rawRequest;

        $weight = $this->getTotalNumOfBoxes($request->getPackageWeight(), $request->getAllItems());
        $weight = $this->_getCorrectWeight($weight);
        $r->setWeight($weight);

        return $this;
    }

    /*
     * Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Ups::_getXmlQuotes()
     * by adding insurance to values over
     */
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
            '19_destPostal'  => Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID == $r->getDestCountry() ?
                substr($r->getDestPostal(), 0, 5) :
                $r->getDestPostal(),
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

        if ($r->getValue() >= $this->getConfigData('force_insurance_value')) {
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

        if ($r->getIsReturn()) {
            $shipperCity = '';
            $shipperPostalCode = $params['19_destPostal'];
            $shipperCountryCode = $params['22_destCountry'];
            $shipperStateProvince = $params['destRegionCode'];
        } else {
            $shipperCity = $params['origCity'];
            $shipperPostalCode = $params['15_origPostal'];
            $shipperCountryCode = $params['14_origCountry'];
            $shipperStateProvince = $params['origRegionCode'];
        }

$xmlRequest .= <<< XMLRequest
      <Address>
          <City>{$shipperCity}</City>
          <PostalCode>{$shipperPostalCode}</PostalCode>
          <CountryCode>{$shipperCountryCode}</CountryCode>
          <StateProvinceCode>{$shipperStateProvince}</StateProvinceCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

          $xmlRequest .= ($params['49_residential']==='01'
                  ? "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>"
                  : ''
          );

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

    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Ups::_parseXmlTrackingResponse()
     * to better handle UPS responses. Includes only showing delivery info for delivered statuses,
     * showing the UPS delivery estimate, and showing the topmost activity in the progress.
     */
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

    /* Extends
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

    protected function _formShipmentRequest(Varien_Object $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Zend_Measure_Weight::POUND ? 'LBS' : 'KGS';
        $dimensionsUnits = $packageParams->getDimensionUnits() == Zend_Measure_Length::INCH ? 'IN' : 'CM';

        $itemsDesc = array();
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new Varien_Object();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        $xmlRequest = new SimpleXMLElement('<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>');
        $requestPart = $xmlRequest->addChild('Request');
        $requestPart->addChild('RequestAction', 'ShipConfirm');
        $requestPart->addChild('RequestOption', $this->getConfigData('ship_request_option'));

        $shipmentPart = $xmlRequest->addChild('Shipment');
        if ($request->getIsReturn()) {
            $returnPart = $shipmentPart->addChild('ReturnService');
            // UPS Print Return Label
            $returnPart->addChild('Code', '9');
        }
        $shipmentPart->addChild('Description', htmlspecialchars(substr(implode(' ', $itemsDesc), 0, 35)));//empirical

        $shipperPart = $shipmentPart->addChild('Shipper');
        if ($request->getIsReturn()) {
            $shipperPart->addChild('Name', $request->getRecipientContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getRecipientContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
            $addressPart->addChild('City', $request->getRecipientAddressCity());
            $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressStateOrProvinceCode());
            }
        } else {
            $shipperPart->addChild('Name', $request->getShipperContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipperPart->addChild('ShipperNumber', $this->getConfigData('shipper_number'));
            $shipperPart->addChild('PhoneNumber', $request->getShipperContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
        }

        $shipToPart = $shipmentPart->addChild('ShipTo');
        $shipToPart->addChild('AttentionName', $request->getRecipientContactPersonName());
        $shipToPart->addChild('CompanyName', $request->getRecipientContactCompanyName()
            ? htmlspecialchars($request->getRecipientContactCompanyName())
            : 'N/A');
        $shipToPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

        $addressPart = $shipToPart->addChild('Address');
        $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet1());
        $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
        $addressPart->addChild('City', $request->getRecipientAddressCity());
        $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
        $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
        if ($request->getRecipientAddressStateOrProvinceCode()) {
            $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressRegionCode());
        }
        if ($this->getConfigData('dest_type') == 'RES') {
            $addressPart->addChild('ResidentialAddress');
        }

        if ($request->getIsReturn()) {
            $shipFromPart = $shipmentPart->addChild('ShipFrom');
            $shipFromPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipFromPart->addChild('CompanyName', $request->getShipperContactCompanyName()
                ? $request->getShipperContactCompanyName()
                : $request->getShipperContactPersonName());
            $shipFromAddress = $shipFromPart->addChild('Address');
            $shipFromAddress->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $shipFromAddress->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $shipFromAddress->addChild('City', $request->getShipperAddressCity());
            $shipFromAddress->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $shipFromAddress->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $shipFromAddress->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }

            $addressPart = $shipToPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $addressPart->addChild('ResidentialAddress');
            }
        }

        $servicePart = $shipmentPart->addChild('Service');
        $servicePart->addChild('Code', $request->getShippingMethod());
        $packagePart = $shipmentPart->addChild('Package');
        $packagePart->addChild('Description', htmlspecialchars(substr(implode(' ', $itemsDesc), 0, 35)));//empirical
        $packagePart->addChild('PackagingType')
            ->addChild('Code', $request->getPackagingType());
        $packageWeight = $packagePart->addChild('PackageWeight');
        $packageWeight->addChild('Weight', $request->getPackageWeight());
        $packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $weightUnits);

        // set dimensions
        if ($length || $width || $height) {
            $packageDimensions = $packagePart->addChild('Dimensions');
            $packageDimensions->addChild('UnitOfMeasurement')->addChild('Code', $dimensionsUnits);
            $packageDimensions->addChild('Length', $length);
            $packageDimensions->addChild('Width', $width);
            $packageDimensions->addChild('Height', $height);
        }

        // ups support reference number only for domestic service
        if ($this->_isUSCountry($request->getRecipientAddressCountryCode())
            && $this->_isUSCountry($request->getShipperAddressCountryCode())
        ) {
            if ($request->getReferenceData()) {
                $referenceCode = $request->hasReferenceCode() ? $request->getReferenceCode() : 'PO';
                $referenceData = $request->getReferenceData();
            } else {
                $referenceCode = 'TN';
                $referenceData = $request->getOrderShipment()->getOrder()->getIncrementId();
            }
            $referencePart = $packagePart->addChild('ReferenceNumber');
            $referencePart->addChild('Code', $referenceCode);
            $referencePart->addChild('Value', $referenceData);
        }

        $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
        if ($deliveryConfirmation) {
            /** @var $serviceOptionsNode SimpleXMLElement */
            $serviceOptionsNode = null;
            switch ($this->_getDeliveryConfirmationLevel($request->getRecipientAddressCountryCode())) {
                case self::DELIVERY_CONFIRMATION_PACKAGE:
                    $serviceOptionsNode = $packagePart->addChild('PackageServiceOptions');
                    break;
                case self::DELIVERY_CONFIRMATION_SHIPMENT:
                    $serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
                    break;
            }
            if (!is_null($serviceOptionsNode)) {
                $serviceOptionsNode
                    ->addChild('DeliveryConfirmation')
                    ->addChild('DCISType', $packageParams->getDeliveryConfirmation());
            }
        }

        $forceInsuranceValue = $this->getConfigData('force_insurance_value');
        if ($forceInsuranceValue && $packageParams->getCustomsValue() >= $forceInsuranceValue) {
            $serviceOptionsNode = isset($packagePart->PackageServiceOptions)
                ? $packagePart->PackageServiceOptions
                : $packagePart->addChild('PackageServiceOptions');

            /* @var $insuredValuePart SimpleXMLElement */
            $insuredValuePart = $serviceOptionsNode->addChild('InsuredValue');
            $insuredValuePart->addChild('Type')
                ->addChild('Code', '01');

            $insuredValuePart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $insuredValuePart->addChild('MonetaryValue', $packageParams->getCustomsValue());
        }

        $shipmentPart->addChild('PaymentInformation')
            ->addChild('Prepaid')
            ->addChild('BillShipper')
            ->addChild('AccountNumber', $this->getConfigData('shipper_number'));

        if ($this->getConfigFlag('negotiated_active')) {
            $shipmentPart->addChild('RateInformation')
                ->addChild('NegotiatedRatesIndicator');
        }

        if ($request->getPackagingType() != $this->getCode('container', 'ULE')
            && $request->getShipperAddressCountryCode() == Mage_Usa_Model_Shipping_Carrier_Abstract::USA_COUNTRY_ID
            && ($request->getRecipientAddressCountryCode() == 'CA' //Canada
                || $request->getRecipientAddressCountryCode() == 'PR' //Puerto Rico
        )) {
            $invoiceLineTotalPart = $shipmentPart->addChild('InvoiceLineTotal');
            $invoiceLineTotalPart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $invoiceLineTotalPart->addChild('MonetaryValue', ceil($packageParams->getCustomsValue()));
        }

        $labelPart = $xmlRequest->addChild('LabelSpecification');
        $labelPart->addChild('LabelPrintMethod')
                ->addChild('Code', 'GIF');
        $labelPart->addChild('LabelImageFormat')
                ->addChild('Code', 'GIF');

        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest . $xmlRequest->asXml();
        return $xmlRequest;
    }

    protected function _sendShipmentAcceptRequest(SimpleXMLElement $shipmentConfirmResponse)
    {
        $xmlRequest = new SimpleXMLElement('<?xml version = "1.0" ?><ShipmentAcceptRequest/>');
        $request = $xmlRequest->addChild('Request');
        $request->addChild('RequestAction', 'ShipAccept');
        $xmlRequest->addChild('ShipmentDigest', $shipmentConfirmResponse->ShipmentDigest);

        $debugData = array('request' => $xmlRequest->asXML());
        try {
            $url = $this->_defaultUrls['ShipAccept'];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_xmlAccessRequest . $xmlRequest->asXML());
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (boolean)$this->getConfigFlag('mode_xml'));
            $xmlResponse = curl_exec ($ch);

            $debugData['result'] = $xmlResponse;
            $this->_setCachedQuotes($xmlRequest, $xmlResponse);
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

        try {
            $response = new SimpleXMLElement($xmlResponse);
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
        }

        $result = new Varien_Object();
        if (isset($response->Error)) {
            $result->setErrors((string)$response->Error->ErrorDescription);
        } else {
            $shippingLabelContent = (string)$response->ShipmentResults->PackageResults->LabelImage->GraphicImage;
            $trackingNumber       = (string)$response->ShipmentResults->PackageResults->TrackingNumber;

            $result->setShippingLabelContent(base64_decode($shippingLabelContent));
            $result->setTrackingNumber($trackingNumber);

            $pkg = Mage::getModel('unl_ship/shipment_package')
                ->setCarrierShipmentId((string)$response->ShipmentResults->ShipmentIdentificationNumber)
                ->setWeightUnits((string)$response->ShipmentResults->BillingWeight->UnitOfMeasurement->Code)
                ->setWeight((string)$response->ShipmentResults->BillingWeight->Weight)
                ->setTrackingNumber($trackingNumber)
                ->setCurrencyUnits((string)$response->ShipmentResults->ShipmentCharges->TotalCharges->CurrencyCode)
                ->setShippingTotal((string)$response->ShipmentResults->ShipmentCharges->TotalCharges->MonetaryValue)
                ->setTransportationCharge((string)$response->ShipmentResults->ShipmentCharges->TransportationCharges->MonetaryValue)
                ->setServiceOptionCharge((string)$response->ShipmentResults->ShipmentCharges->ServiceOptionsCharges->MonetaryValue)
                ->setLabelFormat((string)$response->ShipmentResults->PackageResults->LabelImage->LabelImageFormat->Code)
                ->setHtmlLabelImage(base64_decode((string)$response->ShipmentResults->PackageResults->LabelImage->HTMLImage));

            if (isset($response->ShipmentResults->NegotiatedRates)) {
                $pkg->setNegotiatedTotal((string)$response->ShipmentResults->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue);
            }

            if (isset($response->ShipmentResults->ControlLogReceipt)) {
                $pkg->setInsDoc(base64_decode((string)$response->ShipmentResults->ControlLogReceipt->GraphicImage));
            }

            if (isset($response->ShipmentResults->Form)) {
                $pkg->setIntlDoc(base64_decode((string)$response->ShipmentResults->Form->Image->GraphicImage));
            }

            $result->setPackage($pkg);
        }

        $this->_debug($debugData);
        return $result;
    }
}
