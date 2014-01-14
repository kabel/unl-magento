<?php

class Unl_Ship_Model_Shipping_Carrier_Usps_Endicia
{
    const DEFAULT_MAX_POSTAGE = 500;
    const DEFAULT_MIN_RECREDIT = 10;

    protected $_carrier;

    protected $_form2976 = array(
        'INT_13',
        'INT_14',
        'INT_15',
        'INT_17',
        'INT_18',
        'INT_19',
        'INT_20',
        'INT_21',
        'INT_22',
        'INT_23',
    );

    /**
     * Get the singleton instance of the USPS shippnig carrier
     *
     * @return Unl_Ship_Model_Shipping_Carrier_Usps
     */
    public function getCarrier()
    {
        if (null === $this->_carrier) {
            $this->_carrier = Mage::getSingleton('usa/shipping_carrier_usps');
        }

        return $this->_carrier;
    }

    public function setCarrier($usps)
    {
        $this->_carrier = $usps;
        return $this;
    }

    public function requestBuyPostage($force = false, $currentBalence = null)
    {
        if (is_null($currentBalence)) {
            $status = $this->requestAccountStatus();
            $currentBalence = (float)$status->CertifiedIntermediary->PostageBalance;
        }

        $usps = $this->getCarrier();
        $autoThreshold = $usps->getConfigData('endicia_auto_purchase_threshold');
        $maxBalence = $usps->getConfigData('endicia_max_postage');
        if (empty($maxBalence)) {
            $maxBalence = self::DEFAULT_MAX_POSTAGE;
        }

        if ($force || ($autoThreshold != '' && $currentBalence <= $autoThreshold)) {
            $recreditAmount = $maxBalence - $currentBalence;

            if ($recreditAmount < self::DEFAULT_MIN_RECREDIT) {
                return false;
            }

            $requstId = sha1(microtime() . 'ENDICIA_RECREDIT_REQUEST');
            $xmlRequest = new SimpleXMLElement('<RecreditRequest/>');
            $xmlRequest->addChild('RequesterID', $usps->getConfigData('endicia_requester_id'));
            $xmlRequest->addChild('RequestID', $requstId);

            $certifiedIntermediary = $xmlRequest->addChild('CertifiedIntermediary');
            $certifiedIntermediary->addChild('AccountID', $usps->getConfigData('endicia_account_id'));
            $certifiedIntermediary->addChild('PassPhrase', $usps->getConfigData('endicia_passphrase'));

            $xmlRequest->addChild('RecreditAmount', round($recreditAmount, 2));

            $url = $usps->getConfigData('endicia_els_url') . '/BuyPostageXML';

            $client = new Zend_Http_Client();
            $client->setUri($url);
            $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
            $client->setParameterPost('recreditRequestXML', $xmlRequest->asXML());

            $response = $client->request(Zend_Http_Client::POST);
            $xmlResponse = $response->getBody();

            if ($response->getStatus() != 200) {
                throw new Exception('Invalid Request: ' . $xmlResponse);
            }

            try {
                $xml = @new SimpleXMLElement($xmlResponse);
            } catch (Exception $e) {
                throw new Exception('Invalid Endicia Response');
            }

            if ($xml->Status != 0) {
                throw new Exception($xml->ErrorMessage);
            }

            return true;
        }
    }

    public function requestAccountStatus()
    {
        $usps = $this->getCarrier();
        $requstId = sha1(microtime() . 'ENDICIA_ACCOUNT_STATUS');
        $xmlRequest = new SimpleXMLElement('<AccountStatusRequest/>');
        $xmlRequest->addChild('RequesterID', $usps->getConfigData('endicia_requester_id'));
        $xmlRequest->addChild('RequestID', $requstId);

        $certifiedIntermediary = $xmlRequest->addChild('CertifiedIntermediary');
        $certifiedIntermediary->addChild('AccountID', $usps->getConfigData('endicia_account_id'));
        $certifiedIntermediary->addChild('PassPhrase', $usps->getConfigData('endicia_passphrase'));

        $url = $usps->getConfigData('endicia_els_url') . '/GetAccountStatusXML';

        $client = new Zend_Http_Client();
        $client->setUri($url);
        $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
        $client->setParameterPost('accountStatusRequestXML', $xmlRequest->asXML());

        $response = $client->request(Zend_Http_Client::POST);
        $xmlResponse = $response->getBody();

        if ($response->getStatus() != 200) {
            throw new Exception('Invalid Request: ' . $xmlResponse);
        }

        try {
            $xml = @new SimpleXMLElement($xmlResponse);
        } catch (Exception $e) {
            throw new Exception('Invalid Endicia Response');
        }

        if ($xml->Status != 0) {
            throw new Exception($xml->ErrorMessage);
        }

        return $xml;
    }

    public function requestChangePassPhrase($oldPassPhrase, $newPassPhrase)
    {
        $usps = $this->getCarrier();
        $requstId = sha1(microtime() . 'ENDICIA_PASSPHRASE_CHANGE');

        $xmlRequest = new SimpleXMLElement('<ChangePassPhraseRequest/>');
        $xmlRequest->addChild('RequesterID', $usps->getConfigData('endicia_requester_id'));
        $xmlRequest->addChild('RequestID', $requstId);

        $certifiedIntermediary = $xmlRequest->addChild('CertifiedIntermediary');
        $certifiedIntermediary->addChild('AccountID', $usps->getConfigData('endicia_account_id'));
        $certifiedIntermediary->addChild('PassPhrase', $oldPassPhrase);

        $xmlRequest->addChild('NewPassPhrase', $newPassPhrase);

        $debugData = array('request' => $xmlRequest->asXML());
        try {
            $url = $usps->getConfigData('endicia_els_url') . '/ChangePassPhraseXML';

            $client = new Zend_Http_Client();
            $client->setUri($url);
            $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
            $client->setParameterPost('changePassPhraseRequestXML', $xmlRequest->asXML());

            $response = $client->request(Zend_Http_Client::POST);
            $xmlResponse = $response->getBody();
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

        if ($response->getStatus() != 200) {
            $debugData['result'] = array('error' => 'Invalid Request: ' . $xmlResponse);
            $xml = new SimpleXMLElement('<Error/>');
        } else {
            try {
                $xml = @new SimpleXMLElement($xmlResponse);
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $xml = new SimpleXMLElement('<Error/>');
            }
        }

        $usps->debugData($debugData);

        if (!isset($xml->Status) || $xml->Status != 0) {
            throw new Exception($xml->ErrorMessage);
        }

        return $this;
    }

    public function getMailpieceInfoFromService($service)
    {
        $codes = array(
            '0_FCLE' => array('First', 'Flat'),
            '0_FCL'  => array('First', 'Letter'),
            '0_FCP'  => array('First', 'Parcel'),
            '0_FCPC' => array('First', 'Card'),
            '1'      => array('Priority', null),
            '2'      => array('PriorityExpress', null, array('Hfp')),
            '3'      => array('PriorityExpress', null),
            '4'      => array('ParcelSelect', 'Parcel'),
            '6'      => array('MediaMail', 'Parcel'),
            '7'      => array('LibraryMail', 'Parcel'),
            '13'     => array('PriorityExpress', 'FlatRateEnvelope'),
            '15'     => array('First', 'Card'),
            '16'     => array('Priority', 'FlatRateEnvelope'),
            '17'     => array('Priority', 'MediumFlatRateBox'),
            '22'     => array('Priority', 'LargeFlatRateBox'),
            '23'     => array('PriorityExpress', null, array('Sunday')),
            '25'     => array('PriorityExpress', 'FlatRateEnvelope', array('Sunday')),
            '27'     => array('PriorityExpress', 'FlatRateEnvelope', array('Hfp')),
            '28'     => array('Priority', 'SmallFlatRateBox'),
            '29'     => array('Priority', 'FlatRatePaddedEnvelope'),
            '30'     => array('PriorityExpress', 'FlatRateLegalEnvelope'),
            '31'     => array('PriorityExpress', 'FlatRateLegalEnvelope', array('Hfp')),
            '32'     => array('PriorityExpress', 'FlatRateLegalEnvelope', array('Sunday')),
            '33'     => array('Priority', null, array('Hfp')),
            '34'     => array('Priority', 'LargeFlatRateBox', array('Hfp')),
            '35'     => array('Priority', 'MediumFlatRateBox', array('Hfp')),
            '36'     => array('Priority', 'SmallFlatRateBox', array('Hfp')),
            '37'     => array('Priority', 'FlatRateEnvelope', array('Hfp')),
            '38'     => array('Priority', 'FlatRateGiftCardEnvelope'),
            '39'     => array('Priority', 'FlatRateGiftCardEnvelope', array('Hfp')),
            '40'     => array('Priority', 'FlatRateWindowEnvelope'),
            '41'     => array('Priority', 'FlatRateWindowEnvelope', array('Hfp')),
            '42'     => array('Priority', 'SmallFlatRateEnvelope'),
            '43'     => array('Priority', 'SmallFlatRateEnvelope', array('Hfp')),
            '44'     => array('Priority', 'FlatRateLegalEnvelope'),
            '45'     => array('Priority', 'FlatRateLegalEnvelope', array('Hfp')),
            '46'     => array('Priority', 'FlatRatePaddedEnvelope', array('Hfp')),
            '47'     => array('Priority', 'RegionalRateBoxA'),
            '48'     => array('Priority', 'RegionalRateBoxA', array('Hfp')),
            '49'     => array('Priority', 'RegionalRateBoxB'),
            '50'     => array('Priority', 'RegionalRateBoxB', array('Hfp')),
            '53'     => array('First', 'Parcel', array('Hfp')),
            '55'     => array('PriorityExpress', 'MediumFlatRateBox'),
            '56'     => array('PriorityExpress', 'MediumFlatRateBox', array('Hfp')),
            '57'     => array('PriorityExpress', 'MediumFlatRateBox', array('Sunday')),
            '58'     => array('Priority', 'RegionalRateBoxC'),
            '59'     => array('Priority', 'RegionalRateBoxC', array('Hfp')),
            '61'     => array('First', 'Parcel'),
            '62'     => array('PriorityExpress', 'FlatRatePaddedEnvelope'),
            '63'     => array('PriorityExpress', 'FlatRatePaddedEnvelope', array('Hfp')),
            '64'     => array('PriorityExpress', 'FlatRatePaddedEnvelope', array('Sunday')),
            'INT_1'  => array('PriorityMailExpressInternational', null),
            'INT_2'  => array('PriorityMailInternational', null),
            'INT_4'  => array('GXG', null),
            'INT_6'  => array('GXG', 'Parcel'),
            'INT_7'  => array('GXG', 'Parcel'),
            'INT_8'  => array('PriorityMailInternational', 'FlatRateEnvelope'),
            'INT_9'  => array('PriorityMailInternational', 'MediumFlatRateBox'),
            'INT_10' => array('PriorityMailExpressInternational', 'FlatRateEnvelope'),
            'INT_11' => array('PriorityMailInternational', 'LargeFlatRateBox'),
            'INT_12' => array('GXG', 'FlatRateEnvelope'),
            'INT_13' => array('FirstClassMailInternational', 'Letter'),
            'INT_14' => array('FirstClassMailInternational', 'Flat'),
            'INT_15' => array('FirstClassMailInternational', 'Parcel'),
            'INT_16' => array('PriorityMailInternational', 'SmallFlatRateBox'),
            'INT_17' => array('PriorityMailExpressInternational', 'FlatRateLegalEnvelope'),
            'INT_18' => array('PriorityMailInternational', 'FlatRateGiftCardEnvelope'),
            'INT_19' => array('PriorityMailInternational', 'FlatRateWindowEnvelope'),
            'INT_20' => array('PriorityMailInternational', 'SmallFlatRateEnvelope'),
            'INT_21' => array('FirstClassMailInternational', 'Card'),
            'INT_22' => array('PriorityMailInternational', 'FlatRateLegalEnvelope'),
            'INT_23' => array('PriorityMailInternational', 'FlatRatePaddedEnvelope'),
            'INT_24' => array('PriorityMailInternational', 'DVDFlatRateBox'),
            'INT_25' => array('PriorityMailInternational', 'LargeVideoFlatRateBox'),
            'INT_26' => array('PriorityMailExpressInternational', 'MediumFlatRateBox'),
            'INT_27' => array('PriorityMailExpressInternational', 'FlatRateEnvelope'),
        );

        if (!isset($codes[$service])) {
            return false;
        }

        return $codes[$service];
    }

    /**
     * Sends and processes a label request to Endicia Label Server
     *
     * @param Unl_Ship_Model_Shipping_Carrier_Usps $usps
     * @param Varien_Object $request
     */
    public function doShipmentRequest($usps, $request, $domestic)
    {
        $this->setCarrier($usps);
        $result = new Varien_Object();

        $mailinfo = $this->getMailpieceInfoFromService($request->getShippingMethod());

        if (!$mailinfo) {
            throw new Exception(Mage::helper('usa')->__('Service type does not match'));
        }

        $packageParams = $request->getPackageParams();
        $packageWeight = $request->getPackageWeight();

        if ($packageParams->getWeightUnits() != Zend_Measure_Weight::OUNCE) {
            $packageWeight = Mage::helper('usa')->convertMeasureWeight(
                $request->getPackageWeight(),
                $packageParams->getWeightUnits(),
                Zend_Measure_Weight::OUNCE
            );
            $packageWeight = round($packageWeight, 1);
        }

        $labelFormat = 'PNG';

        $xmlRequest = new SimpleXMLElement('<LabelRequest/>');
        $xmlRequest->addAttribute('ImageFormat', $labelFormat . 'MONOCHROME');

        if ($domestic) {
            $xmlRequest->addAttribute('LabelType', 'Default');
            $xmlRequest->addAttribute('LabelSize', '4x6');

        } else {
            $xmlRequest->addAttribute('LabelType', 'International');
            $xmlRequest->addAttribute('LabelSubtype', 'Integrated');
            $xmlRequest->addAttribute('LabelSize', '4x6c');
            $xmlRequest->addAttribute('ImageRotation', 'Rotate270');
        }

        if ($usps->getConfigFlag('endicia_test_mode')) {
            $xmlRequest->addAttribute('Test', 'YES');
        }

        $xmlRequest->addChild('RequesterID', $usps->getConfigData('endicia_requester_id'));
        $xmlRequest->addChild('AccountID', $usps->getConfigData('endicia_account_id'));
        $xmlRequest->addChild('PassPhrase', $usps->getConfigData('endicia_passphrase'));

        $xmlRequest->addChild('MailClass', $mailinfo[0]);
        $xmlRequest->addChild('WeightOz', $packageWeight);

        if (!is_null($mailinfo[1])) {
            $xmlRequest->addChild('MailpieceShape', $mailinfo[1]);
        }

        if ($mailinfo[0] == 'ParcelSelect') {
            $xmlRequest->addChild('SortType', 'Nonpresorted');
            $xmlRequest->addChild('EntryFacility', 'Other');
        }

        if ($packageParams->getLength() || $packageParams->getWidth() || $packageParams->getHeight()) {
            if ($packageParams->getDimensionUnits() != Zend_Measure_Length::INCH) {
                $length = round(Mage::helper('usa')->convertMeasureDimension(
                    $packageParams->getLength(),
                    $packageParams->getDimensionUnits(),
                    Zend_Measure_Length::INCH
                ));
                $width = round(Mage::helper('usa')->convertMeasureDimension(
                    $packageParams->getWidth(),
                    $packageParams->getDimensionUnits(),
                    Zend_Measure_Length::INCH
                ));
                $height = round(Mage::helper('usa')->convertMeasureDimension(
                    $packageParams->getHeight(),
                    $packageParams->getDimensionUnits(),
                    Zend_Measure_Length::INCH
                ));
            } else {
                $length = round($packageParams->getLength());
                $width = round($packageParams->getWidth());
                $height = round($packageParams->getHeight());
            }
            $dimensions = $xmlRequest->addChild('MailpieceDimensions');
            $dimensions->addChild('Length', $length);
            $dimensions->addChild('Width', $width);
            $dimensions->addChild('Height', $height);
        }

        if (!$domestic) {
            if (in_array($request->getShippingMethod(), $this->_form2976)) {
                $xmlRequest->addChild('IntegratedFormType', 'FORM2976');
            } else {
                $xmlRequest->addChild('IntegratedFormType', 'FORM2976A');
            }
            $customsInfo = $xmlRequest->addChild('CustomsInfo');
            $customsInfo->addChild('ContentsType', $packageParams->getContentType());
            if ($packageParams->getContentType() == 'OTHER') {
                $customsInfo->addChild('ContentsExplanation', $packageParams->getContentTypeOther());
            }

            $customsItems = $customsInfo->addChild('CustomsItems');
            foreach ($request->getPackageItems() as $item) {
                $cItem = $customsItems->addChild('CustomsItem');
                $cItem->addChild('Description', substr($item['name'], 0, 50));
                $cItem->addChild('Quantity', $item['qty']);
                $cItem->addChild('Weight', floor(Mage::helper('usa')->convertMeasureWeight(
                    $item['weight'],
                    Zend_Measure_Weight::POUND,
                    Zend_Measure_Weight::OUNCE
                )));
                $cItem->addChild('Value', round($item['customs_value'] * $item['qty'], 2));
            }

            //$xmlRequest->addChild('Value', $packageParams->getCustomsValue());
            //$xmlRequest->addChild('Description', 'Order # ' . $request->getOrderShipment()->getOrder()->getIncrementId());
        }

        if ($packageParams->getDeliveryConfirmation() === 'False') {
            $xmlRequest->addChild('Services')
                ->addAttribute('SignatureConfirmation', 'ON');
        }

        $xmlRequest->addChild('ReferenceID', $request->getOrderShipment()->getOrder()->getIncrementId());
        $xmlRequest->addChild('RubberStamp1', 'Order # ' . $request->getOrderShipment()->getOrder()->getIncrementId());

        $requestId = substr(sha1(microtime() . $request->getShipperEmail() . 'ENDICIA_LABEL_REQUEST'), 0, 25);
        $xmlRequest->addChild('PartnerCustomerID', $request->getShipperContactPhoneNumber());
        $xmlRequest->addChild('PartnerTransactionID', $requestId);

        $xmlRequest->addChild('ResponseOptions')
            ->addAttribute('PostagePrice', 'TRUE');

        $xmlRequest->addChild('FromName', $request->getShipperContactPersonName());
        if ($request->getShipperContactCompanyName()) {
            $xmlRequest->addChild('FromCompany', $request->getShipperContactCompanyName());
        }
        $xmlRequest->addChild('ReturnAddress1', $request->getShipperAddressStreet1());
        $xmlRequest->addChild('ReturnAddress2', $request->getShipperAddressStreet2());
        $xmlRequest->addChild('FromCity', $request->getShipperAddressCity());
        $xmlRequest->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xmlRequest->addChild('FromPostalCode', $request->getShipperAddressPostalCode());
        if ($request->getShipperAddressCountryCode() != 'US') {
            $xmlRequest->addChild('FromCountry', $request->getShipperAddressCountryCode());
        }
        $xmlRequest->addChild('FromPhone', $request->getShipperContactPhoneNumber());
        $xmlRequest->addChild('FromEMail', $request->getShipperEmail());

        $postalCode = $request->getRecipientAddressPostalCode();
        if ($domestic) {
            $postalCode = explode('-', $postalCode);
            $toZip5 = substr($postalCode[0], 0, 5);
            $toZip4 = isset($postalCode[1]) ? $postalCode[1] : substr($postalCode[0], 5, 4);
        }
        $xmlRequest->addChild('ToName', $request->getRecipientContactPersonName());
        $xmlRequest->addChild('ToCompany', $request->getRecipientContactCompanyName());
        $xmlRequest->addChild('ToAddress1', $request->getRecipientAddressStreet1());
        $xmlRequest->addChild('ToAddress2', $request->getRecipientAddressStreet2());

        $city = $request->getRecipientAddressCity();
        if ($domestic) {
            $city = preg_replace('/[^a-zA-Z \-\.]/', '', $city);
        }
        $xmlRequest->addChild('ToCity', $city);

        if ($request->getRecipientAddressStateOrProvinceCode()) {
            $xmlRequest->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        }
        $xmlRequest->addChild('ToPostalCode', $domestic ? $toZip5 : $postalCode);
        if ($domestic && $toZip4) {
            $xmlRequest->addChild('ToZip4', $toZip4);
        }
        if ($request->getRecipientAddressCountryCode() != 'US') {
            //$toCountry = Mage::getModel('directory/country')->loadByCode($request->getRecipientAddressCountryCode());
            //$xmlRequest->addChild('ToCountry', $toCountry->getName());
            $xmlRequest->addChild('ToCountryCode', $request->getRecipientAddressCountryCode());
        }
        $xmlRequest->addChild('ToPhone', $request->getRecipientContactPhoneNumber());
        $xmlRequest->addChild('ToEMail', $request->getRecipientEmail());

        $debugData = array('request' => $xmlRequest->asXML());

        try {
            $url = $usps->getConfigData('endicia_els_url') . '/GetPostageLabelXML';

            $client = new Zend_Http_Client();
            $client->setUri($url);
            $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
            $client->setParameterPost('labelRequestXML', $xmlRequest->asXML());

            $response = $client->request(Zend_Http_Client::POST);
            $xmlResponse = $response->getBody();
        } catch (Exception $e) {
            $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
            $xmlResponse = '';
        }

        if ($response->getStatus() != 200) {
            $debugData['result'] = array('error' => 'Invalid Request: ' . $xmlResponse);
            $xml = new SimpleXMLElement('<Error/>');
        } else {
            try {
                $xml = @new SimpleXMLElement($xmlResponse);
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $xml = new SimpleXMLElement('<Error/>');
            }
        }

        if (!isset($xml->Status) || $xml->Status != 0) {
            $result->setErrors((string)$xml->ErrorMessage);
            $debugData['result'] = array('error' => $result->getErrors());
        } else {
            $debugData['result'] = $xmlResponse;

            if (isset($xml->Base64LabelImage)) {
                $labelContent = base64_decode((string)$xml->Base64LabelImage);
            } else {
                $intlDoc = new Zend_Pdf();
                foreach ($xml->Label->Image as $img) {
                    if ((string)$img['PartNumber'] == '1') {
                        $labelContent = base64_decode((string)$img);
                    } else {
                        Mage::helper('unl_ship/pdf')->attachImagePage($intlDoc, new Zend_Pdf_Resource_Image_Png('data://image/png;base64,' . (string)$img));
                    }
                }
            }

            $trackingNumber = (string)$xml->TrackingNumber;

            $result->setShippingLabelContent($labelContent);
            $result->setTrackingNumber($trackingNumber);

            $pkg = Mage::getModel('unl_ship/shipment_package')
                ->setCarrierShipmentId((string)$xml->PIC)
                ->setWeightUnits('OZ')
                ->setWeight($packageWeight)
                ->setTrackingNumber($trackingNumber)
                ->setCurrencyUnits('USD')
                ->setShippingTotal((string)$xml->PostagePrice['TotalAmount'])
                ->setTransportationCharge(0)
                ->setServiceOptionCharge((string)$xml->PostagePrice->Fees['TotalAmount'])
                ->setLabelFormat($labelFormat);

            if (isset($intlDoc) && count($intlDoc->pages)) {
                $pkg->setIntlDoc($intlDoc->render());
            }

            $result->setPackage($pkg);

            try {
                $this->requestBuyPostage(false, (float)$xml->PostageBalance);
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        $usps->debugData($debugData);

        return $result;
    }

    public function doRefundRequest($usps, $trackingNumber)
    {
        $result = new Varien_Object();
        $xmlRequest = new SimpleXMLElement('<RefundRequest/>');
        $xmlRequest->addChild('AccountID', $usps->getConfigData('endicia_account_id'));
        $xmlRequest->addChild('PassPhrase', $usps->getConfigData('endicia_passphrase'));

        if ($usps->getConfigFlag('endicia_test_mode')) {
            $xmlRequest->addChild('Test', 'Y');
        }

        $refundList = $xmlRequest->addChild('RefundList');
        $refundList->addChild('PICNumber', $trackingNumber);

        $debugData = array('request' => $xmlRequest->asXML());

        $url = $usps->getConfigData('endicia_els_int_url') . '&method=RefundRequest';

        $client = new Zend_Http_Client();
        $client->setUri($url);
        $client->setConfig(array('maxredirects' => 0, 'timeout' => 30));
        $client->setParameterPost('XMLInput', $xmlRequest->asXML());

        $response = $client->request(Zend_Http_Client::POST);
        $xmlResponse = $response->getBody();

        if ($response->getStatus() != 200) {
            $debugData['result'] = array('error' => 'Invalid Request: ' . $xmlResponse);
            $result->setErrors('Invalid Request');
        } else {
            try {
                $xml = @new SimpleXMLElement($xmlResponse);
            } catch (Exception $e) {
                $debugData['result'] = array('error' => $e->getMessage(), 'code' => $e->getCode());
                $result->setErrors('Bad Response');
            }
        }

        if (!$result->hasErrors()) {
            if ((string)$xml->ErrorMsg != '') {
                $result->setErrors((string)$xml->ErrorMsg);
                $debugData['result'] = array('error' => $result->getErrors());
            } elseif ($xml->RefundList->PICNumber->IsApproved != 'YES') {
                $result->setErrors((string)$xml->RefundList->PICNumber->ErrorMsg);
                $debugData['result'] = array('error' => $result->getErrors());
            } else {
                $result->setMessage(Mage::helper('unl_ship')->__('Endicia refund request submitted with ID: "%s".', (string)$xml->FormNumber));
            }
        }

        $usps->debugData($debugData);

        return $result;
    }
}
