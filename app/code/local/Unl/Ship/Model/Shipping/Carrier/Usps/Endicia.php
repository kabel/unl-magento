<?php

class Unl_Ship_Model_Shipping_Carrier_Usps_Endicia
{
    const DEFAULT_MAX_POSTAGE = 500;
    const DEFAULT_MIN_RECREDIT = 10;

    protected $_carrier;

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
            'First-Class'                                   => array('First', null),
            'First-Class Mail International Large Envelope' => array('FirstClassMailInternational', 'Parcel'),
            'First-Class Mail International Letter'         => array('FirstClassMailInternational', 'Letter'),
            'First-Class Mail International Package'        => array('FirstClassMailInternational', 'Parcel'),
            'First-Class Mail International Parcel'         => array('FirstClassMailInternational', 'Parcel'),
            'First-Class Mail'                 => array('First', null),
            'First-Class Mail Flat'            => array('First', 'Flat'),
            'First-Class Mail Large Envelope'  => array('First', 'Parcel'),
            'First-Class Mail International'   => array('FirstClassMailInternational', 'Letter'),
            'First-Class Mail Letter'          => array('First', 'Letter'),
            'First-Class Mail Parcel'          => array('First', 'Parcel'),
            'First-Class Mail Package'         => array('First', 'Parcel'),
            'Parcel Post'                      => array('ParcelPost', 'Parcel'),
            'Bound Printed Matter'             => false,
            'Media Mail'                       => array('MediaMail', 'Parcel'),
            'Library Mail'                     => array('LibraryMail', 'Parcel'),
            'Express Mail'                     => array('Express', null),
            'Express Mail PO to PO'            => array('Express', null),
            'Express Mail Flat Rate Envelope'  => array('Express', 'FlatRateEnvelope'),
            'Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee'  => array('Express', 'FlatRateEnvelope', array('Sunday')),
            'Express Mail Sunday/Holiday Guarantee'            => array('Express', null, array('Sunday')),
            'Express Mail Flat Rate Envelope Hold For Pickup'  => array('Express', 'FlatRateEnvelope', array('Hfp')),
            'Express Mail Hold For Pickup'                     => array('Express', null, array('Hfp')),
            'Global Express Guaranteed (GXG)'                  => array('GXG', null),
            'Global Express Guaranteed Non-Document Rectangular'     => array('GXG', 'Parcel'),
            'Global Express Guaranteed Non-Document Non-Rectangular' => array('GXG', 'IrregularParcel'),
            'USPS GXG Envelopes'                               => array('GXG', 'Letter'),
            'Express Mail International'                       => array('ExpressMailInternational', null),
            'Express Mail International Flat Rate Envelope'    => array('ExpressMailInternational', 'FlatRateEnvelope'),
            'Priority Mail'                        => array('Priority', null),
            'Priority Mail Small Flat Rate Box'    => array('Priority', 'SmallFlatRateBox'),
            'Priority Mail Medium Flat Rate Box'   => array('Priority', 'MediumFlatRateBox'),
            'Priority Mail Large Flat Rate Box'    => array('Priority', 'LargeFlatRateBox'),
            'Priority Mail Flat Rate Box'          => array('Priority', 'MediumFlatRateBox'),
            'Priority Mail Flat Rate Envelope'     => array('Priority', 'FlatRateEnvelope'),
            'Priority Mail International'                            => array('PriorityMailInternational', null),
            'Priority Mail International Flat Rate Envelope'         => array('Priority', 'FlatRateEnvelope'),
            'Priority Mail International Small Flat Rate Box'        => array('Priority', 'SmallFlatRateBox'),
            'Priority Mail International Medium Flat Rate Box'       => array('Priority', 'MediumFlatRateBox'),
            'Priority Mail International Large Flat Rate Box'        => array('Priority', 'LargeFlatRateBox'),
            'Priority Mail International Flat Rate Box'              => array('Priority', 'MediumFlatRateBox'),
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

        $xmlRequest = new SimpleXMLElement('<LabelRequest/>');
        $xmlRequest->addAttribute('LabelType', 'Default');
        $xmlRequest->addAttribute('LabelSize', '4x6');
        $xmlRequest->addAttribute('ImageFormat', 'PNG');

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

        if (!$domestic) {
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
            }
            $dimensions = $xmlRequest->addChild('MailpieceDimensions');
            $dimensions->addChild('Length', $length);
            $dimensions->addChild('Width', $width);
            $dimensions->addChild('Height', $height);
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

        $xmlRequest->addChild('FromName', htmlspecialchars($request->getShipperContactPersonName()));
        if ($request->getShipperContactCompanyName()) {
            $xmlRequest->addChild('FromCompany', htmlspecialchars($request->getShipperContactCompanyName()));
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
        $xmlRequest->addChild('ToName', htmlspecialchars($request->getRecipientContactPersonName()));
        $xmlRequest->addChild('ToCompany', htmlspecialchars($request->getRecipientContactCompanyName()));
        $xmlRequest->addChild('ToAddress1', $request->getRecipientAddressStreet1());
        $xmlRequest->addChild('ToAddress2', $request->getRecipientAddressStreet2());
        $xmlRequest->addChild('ToCity', $request->getRecipientAddressCity());
        $xmlRequest->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        $xmlRequest->addChild('ToPostalCode', $domestic ? $toZip5 : $postalCode);
        if ($domestic && $toZip4) {
            $xmlRequest->addChild('ToZip4', $toZip4);
        }
        if ($request->getRecipientAddressCountryCode() != 'US') {
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

            $labelContent = base64_decode((string)$xml->Base64LabelImage);
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
                ->setLabelFormat('PNG');

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
            }
        }

        $usps->debugData($debugData);

        return $result;
    }
}
