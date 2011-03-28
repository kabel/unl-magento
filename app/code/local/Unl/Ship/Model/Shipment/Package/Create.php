<?php

class Unl_Ship_Model_Shipment_Package_Create
{
	/**
	 * Processes form data to create a shipment and packages
	 *
	 * @param Mage_Sales_Model_Order $order
	 * @param array $post
	 * @return string A JSON string
	 */
	public function createShipment(Mage_Sales_Model_Order $order, $post)
	{
	    $jsonObj = array();
	    try {
    	    if (!$order->canShip() || !Mage::helper('unl_ship')->isOrderSupportAutoShip($order)) {
    	        throw Mage::exception('Unl_Ship', Mage::helper('sales')->__('This order cannot be shipped.'), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_FATAL);
    	    }

    	    foreach ($post['packages'] as &$package) {
    	        $package['items'] = array();
    	    }
    	    unset($package);

    	    foreach ($post['package_items'] as $itemId => $item) {
    	        $orderItem = $order->getItemById($itemId);
    	        if (!$orderItem->getId() || !$orderItem->canShip()) {
    	            throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Invalid item data recieved. Please refresh this form.'), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_FATAL);
    	        }

    	        $totalItemQty = 0;
    	        foreach ($item['idx'] as $i => $packageIndex) {
    	            if (empty($packageIndex) || empty($item['qty'][$i])) {
    	                continue;
    	            }
        	        if (!isset($post['packages'][$packageIndex])) {
        	            throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Invalid package data recieved. Please confirm your selections and try again.'), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
        	        }

        	        if ($orderItem->getIsQtyDecimal()) {
        	            $qty = (float) $item['qty'][$i];
        	        } else {
        	            $qty = (int) $item['qty'][$i];
        	        }

        	        if (!isset($post['packages'][$packageIndex]['items'][$itemId])) {
        	            $post['packages'][$packageIndex]['items'][$itemId] = 0;
        	        }
        	        $post['packages'][$packageIndex]['items'][$itemId] += $qty;

        	        $totalItemQty += $qty;
    	        }
    	        if ($orderItem->getQtyToShip() < $totalItemQty) {
    	            throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Qty to Ship for item "%s" is invalid. You may need to refresh.', $orderItem->getSku()), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
    	        }
    	    }

    	    $packages = array();
    	    foreach ($post['packages'] as $packageIndex => $package) {
    	        if (empty($package['items'])) {
    	            continue;
    	        }

    	        foreach ($package['items'] as $itemId => $qty) {
    	            $orderItem = $order->getItemById($itemId);
    	            $itemWeight = $post['package_items'][$itemId]['weight'];
    	            if ($itemWeight <= 0) {
    	                throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Item weight for "%s" must be greater than 0.', $orderItem->getSku()), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
    	            }

        	        if (empty($package['value'])) {
        	            $package['value'] = 0;
        	        }
        	        $package['value'] += ($orderItem->getBasePrice() * $qty);

        	        if (empty($package['weight'])) {
        	            $package['weight'] = (float) $package['weight_extra'];
        	        }
        	        $package['weight'] += ($itemWeight * $qty);
    	        }

    	        $packageObj = new Varien_Object(array(
    	            'package_index' => $packageIndex,
    	            'items' => $package['items'],
    	            'container_code' => $package['container'],
    	            'weight' => $package['weight'],
    	            'length' => $package['length'],
    	            'width' => $package['width'],
    	            'height' => $package['height'],
    	            'value'  => $package['value']
    	        ));
    	        //TODO: Implement Frontend for adding shipping options (insurance/signature/etc)

    	        $packages[] = $packageObj;
    	    }

    	    if (empty($packages)) {
    	        throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Invalid package data recieved. Please confirm your selections and try again.'), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
    	    }

            $request = Mage::getModel('unl_ship/shipment_request')
                ->setOrder($order)
                ->setPackages($packages);


            if (isset($post['shipping_address'])) {
                $saveToOrder = isset($post['shipping_address']['save_to_order']);
                $address = $order->getShippingAddress();
                $address->addData($post['shipping_address']);
            }

            $carrier = $order->getShippingCarrier();
            $numRetrys = 0;
            do {
                $retryOk = false;
                try {
                    $results = $carrier->createShipment($request);
                } catch (Mage_Shipping_Exception $e) {
                    if ($e->getCode() && $carrier->isRequestRetryAble($e->getCode()) && !$carrier->isErrorRetried($e->getCode())) {
                        $retryOk = true;
                        $numRetrys++;
                        $carrier->addErrorRetry($e->getCode());
                    } else {
                        throw Mage::exception('Unl_Ship', $e->getMessage(), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
                    }
                }
            } while ($retryOk && $numRetrys < 10);

            if (empty($results)) {
                if (!empty($e)) {
                    throw Mage::exception('Unl_Ship', $e->getMessage(), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
                } else {
                    throw Mage::exception('Unl_Ship', Mage::helper('unl_ship')->__('Unknown error occured.'), Unl_Ship_Exception::SHIPMENT_CREATE_ERROR_NONFATAL);
                }
            }

    	    $resitems = array();
            $tracks = array();
            foreach ($packages as $reqpackage) {
                if (!is_string($results[$reqpackage->getPackageIndex()])) {
                    $tracks[] = $results[$reqpackage->getPackageIndex()]['tracking_number'];
                    foreach ($reqpackage->getItems() as $itemId => $qty) {
                        if (!isset($resitems[$itemId])) {
                            $resitems[$itemId] = 0;
                        }
                        $resitems[$itemId] += $qty;
                    }
                }
            }

            if ($address && $saveToOrder) {
                $address->implodeStreetAddress();
                $address->save();
            }

            //create an order shipment
            $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment($resitems);

            //add the tracking numbers
            foreach ($tracks as $trackingNumber) {
                $track = Mage::getModel('sales/order_shipment_track')
                    ->setCarrierCode($carrier->getCarrierCode())
                    ->setTitle($carrier->getConfigData('title'))
                    ->setNumber($trackingNumber)
                    ->setShipment($shipment);
                $shipment->addTrack($track);
            }

            //save the shipment
            $shipment->register();

            $comment = '';
            if (!empty($post['shipment']['comment_text'])) {
                $shipment->addComment($post['shipment']['comment_text'], isset($post['shipment']['comment_customer_notify']));
                if (isset($post['shipment']['comment_customer_notify'])) {
                    $comment = $post['shipment']['comment_text'];
                }
            }

            $notify = !empty($post['shipment']['send_email']);
            $shipment->setEmailSent($notify);
            $shipment->getOrder()->setCustomerNoteNotify($notify);

            $shipment->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

            //send shipment email
            $shipment->sendEmail($notify, $comment);

            foreach ($results as $pkg) {
                if (!is_string($pkg)) {
                    $pkg->setShipmentId($shipment->getId())
                        ->save();
                }
            }

    	    $jsonObj['result'] = Unl_Ship_Exception::SHIPMENT_CREATE_SUCCESS;
    	    $jsonObj['messages'] = array();
    	    $jsonObj['packages'] = array();
    	    $tracks = array();
    	    foreach ($results as $packageIndex => $pkg) {
    	        if (is_string($pkg)) {
    	            $jsonObj['messages'][] = array(
    	                'type' => 'error',
    	                'message' => Mage::helper()->__('Package # %d: ', $packageIndex) . $pkg
    	            );
    	            $jsonObj['packages'][$packageIndex] = false;
    	            continue;
    	        }

    	        $pkgBlock = Mage::app()->getLayout()->createBlock('unl_ship/shipment_create_response_package', '', array('package' => $pkg));

    	        $jsonObj['packages'][$packageIndex] = array(
    	            'id' => $pkg->getId(),
    	            'tracking' => $pkg->getTrackingNumber(),
    	            'content' => $pkgBlock->toHtml()
    	        );
    	        $tracks[$pkg->getId()] = $pkg->getTrackingNumber();
    	    }
	        $jsonObj['messages'][] = array(
                'type' => 'success',
                'message' => Mage::helper('unl_ship')->__('%d package(s) created with tracking number(s): %s', count($tracks), implode(', ', $tracks))
            );
            $url = Mage::getModel('adminhtml/url')->getUrl('unlship/index/labelpdf', array('id' => implode('|', array_keys($tracks))));
            $jsonObj['button'] = Mage::app()->getLayout()->createBlock('adminhtml/widget_button', '', array(
                'label' => Mage::helper('unl_ship')->__('Print Labels'),
                'class' => 'print-button',
                'onclick' => "printAllLabels(event, '{$url}')"
            ))->toHtml();

        } catch (Unl_Ship_Exception $e) {
            $jsonObj['result'] = $e->getCode();
	        $jsonObj['messages'] = array(array(
	            'type' => 'error',
	            'message' => $e->getMessage()
	        ));
        }

        $jsonObj = new Varien_Object($jsonObj);
        return $jsonObj->toJson();
	}
}
