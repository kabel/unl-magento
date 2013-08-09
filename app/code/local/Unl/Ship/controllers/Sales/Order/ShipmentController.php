<?php

require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';

class Unl_Ship_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    /**
     * The AJAX response object for saving shipments
     *
     * @var Varien_Object
     */
    protected $_responseAjax;

    /* Overrides
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::saveAction()
     * by moving the _saveShipment logic and changing the AJAX response
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }
            $responseAjax = $this->_responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));

            // ** check for invoice capturing
            if (!empty($data['do_invoice'])) {
                $captureCase = isset($data['capture_case']) ? $data['capture_case'] : null;
                $this->_doInvoice($shipment, $captureCase);
            }
            // **

            $this->_saveShipment($shipment, $isNeedCreateLabel);

            $shipment->sendEmail(!empty($data['send_email']), $comment);

            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }

        $shipmentCreatedMessage = $this->__('The shipment has been created.');
        $labelCreatedMessage    = $this->__('The shipping label has been created.');

        $success = $isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage : $shipmentCreatedMessage;
        if ($isNeedCreateLabel) {
            $responseAjax->addData(array(
                'success' => $success,
                'shipment_url' => $this->getUrl('*/*/view', array('shipment_id' => $shipment->getId())),
                'shipping_label_url' => $this->getUrl('*/*/printLabel', array('shipment_id' => $shipment->getId())),
            ));

            $timer = 15;
            if (Mage::helper('unl_ship')->isUnlShipQueueEmpty()) {
                $nextUrl = $this->getUrl('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
                $note = Mage::helper('unl_ship')->__('You will be redirected back to the order in <span class="timer">%s</span> seconds', $timer);
            } else {
                $nextUrl = $this->getUrl('*/*/nextInQueue');
                $note = Mage::helper('unl_ship')->__('You will be redirected to the next order in the queue in <span class="timer">%s</span> seconds', $timer);
            }
            $responseAjax->addData(array(
                'note' => $note,
                'next_url' => $nextUrl,
                'timer' => $timer,
            ));

            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_getSession()->addSuccess($success);
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    }

    /**
     * Searches an array of shipment items for a matching order item id
     *
     * @param Mage_Sales_Model_Order_Shipment_Item[] $items
     * @param int $orderItemId
     * @return Mage_Sales_Model_Order_Shipment_Item
     */
    protected function _getShipmentItemByOrderItemId($items, $orderItemId)
    {
        foreach ($items as $item) {
            if ($item->getOrderItemId() == $orderItemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Creates/captures an invoice for the shipment's items
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $captureCase
     * @throws Exception
     */
    protected function _doInvoice($shipment, $captureCase)
    {
        $helper = Mage::helper('unl_ship/adminhtml_sales_workflow');
        $order = $shipment->getOrder();

        if ($helper->canInvoice($order)) {
            $savedQtys = array();
            if ($order->getPayment()->canCapturePartial()) {
                foreach ($shipment->getAllItems() as $item) {
                    if (!$item->getOrderItem()->isDummy()) {
                        if ($item->getOrderItem()->isDummy(true)) {
                            $parentOrderItem = $item->getOrderItem()->getParentItem();
                            $savedQtys[$item->getOrderItemId()] = $item->getOrderItem()->getQtyOrdered()
                            / $parentOrderItem->getQtyOrdered()
                            * $this->_getShipmentItemByOrderItemId(
                                $shipment->getAllItems(),
                                $parentOrderItem->getId()
                            )->getQty();
                        } else {
                            $savedQtys[$item->getOrderItemId()] = $item->getQty();
                        }
                    }
                }

                foreach ($order->getAllItems() as $item) {
                    if (!isset($savedQtys[$item->getId()]) && $item->getQtyToInvoice()) {
                        $savedQtys[$item->getId()] = 0;
                    }
                }
            }

            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);

            if (!empty($captureCase)) {
                $invoice->setRequestedCaptureCase($captureCase);
            }

            $rethrow = true;
            try {
                $invoice->register();
                $invoice->save();

                $rethrow = false;
                $invoice->sendEmail(false);
            } catch (Exception $e) {
                Mage::logException($e);

                if ($rethrow) {
                    throw $e;
                }
            }
        } elseif ($helper->hasInvoiceNeedsCapture($order)) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                if ($invoice->canCapture()) {
                    if ($captureCase == Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE) {
                        $invoice->capture()->save();
                    } elseif ($captureCase == Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE) {
                        $invoice->setCanVoidFlag(false);
                        $invoice->pay()->save();
                    }
                }
            }
        }
    }

    /* Overrides
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::_saveShipment()
     * by adding a commit callback to create the label if needed
     */
    protected function _saveShipment($shipment, $isNeedCreateLabel = false)
    {
        $shipment->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($shipment)
            ->addObject($shipment->getOrder());

        if ($isNeedCreateLabel) {
            $transactionSave->addCommitCallback(array($this, 'createLabelFromCurrent'));
        }

        $transactionSave->save();

        return $this;
    }

    /**
     * Create shipping label for the current shipment being saved.
     *
     * @return boolean
     */
    public function createLabelFromCurrent()
    {
        $shipment = Mage::registry('current_shipment');
        $responseAjax = $this->_responseAjax;

        if ($responseAjax && $this->_createShippingLabel($shipment)) {
            $shipment->save();
            $responseAjax->setOk(true);

            return true;
        }

        return false;
    }

    /* Extends
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::printLabelAction()
     * by optionally forwarding the request to a different PDF generator
     */
    public function printLabelAction()
    {
        if (!Mage::getStoreConfigFlag('sales/shipment_label/use_unl')) {
            parent::printLabelAction();
            return;
        }

        $this->_forward('labelPdf', 'sales_order_package');
    }

    /* Extends
     * @see Mage_Adminhtml_Sales_Order_ShipmentController::massPrintShippingLabelAction()
     * by optionally forwarding the request to a different PDF generator
     */
    public function massPrintShippingLabelAction()
    {
        if (!Mage::getStoreConfigFlag('sales/shipment_label/use_unl')) {
            parent::massPrintShippingLabelAction();
            return;
        }

        $request = $this->getRequest();
        $createdFromOrders = true;
        $ids = array();
        /* @var $packages Unl_Ship_Model_Resource_Shipment_Package_Collection */
        switch ($request->getParam('massaction_prepare_key')) {
            case 'shipment_ids':
                $createdFromOrders = false;
                $ids = $request->getParam('shipment_ids');
                $filter = 'shipment_id';
                break;
            case 'order_ids':
                $ids = $request->getParam('order_ids');
                $filter = 'order_id';
                break;
        }

        array_filter($ids, 'intval');
        if (!empty($ids)) {
            $packages = Mage::getResourceModel('unl_ship/shipment_package_collection');
            $packages->selectNoData()
                ->addFieldToFilter($filter, array('in' => $ids));

            $ids = $packages->getAllIds();
        }

        if (!empty($ids)) {
            $this->_forward('labelPdf', 'sales_order_package', null, array('id' => implode('|', $ids)));
            return;
        } else {
            $createdFromPartErrorMsg = $createdFromOrders ? 'orders' : 'shipments';
            $this->_getSession()
                ->addError(Mage::helper('sales')->__('There are no shipping labels related to selected %s.', $createdFromPartErrorMsg));
        }

        if ($createdFromOrders) {
            $this->_redirect('*/sales_order/index');
        } else {
            $this->_redirect('*/sales_order_shipment/index');
        }
    }

    public function queueOrdersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
        if (!empty($orderIds)) {
            $helper = Mage::helper('unl_ship');
            $queue = $helper->getUnlShipQueue();
            $count = 0;
            if (!$queue) {
                $queue = array();
            }

            $collection = Mage::getModel('sales/order')->getResourceCollection();
            $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
            foreach ($collection as $order) {
                if ($order->canShip()) {
                    $count++;
                    $queue[] = $order->getId();
                }
            }
            if ($count) {
                $this->_getSession()->addSuccess($this->__('%s order(s) have been queued for auto ship.', $count));
                $orderId = array_shift($queue);
                $helper->setUnlShipQueue($queue);
                return $this->_redirect('*/*/new', array(
                    'order_id' => $orderId
                ));
            } else {
                $this->_getSession()->addError($this->__('There are no shippable orders in the selected orders.'));
                return $this->_redirect('*/sales_order/');
            }

        }

        $this->_redirect('*/sales_order/');
    }

    public function nextInQueueAction()
    {
        $helper = Mage::helper('unl_ship');
        if (!$helper->isUnlShipQueueEmpty()) {
            $orderId = $helper->dequeueUnlShipQueue();
            $this->_redirect('*/*/new', array(
                'order_id' => $orderId
            ));
        } else {
            $this->_getSession()->addError($this->__('The auto ship queue is empty.'));
            $this->_redirect('*/sales_order/');
        }
    }

    public function clearQueueAction()
    {
        Mage::helper('unl_ship')->getUnlShipQueue(true);
        $this->_getSession()->addSuccess($this->__('Successfully cleared the auto ship queue.'));
        $this->_redirect('adminhtml/sales_order/');
    }

    public function voidAction()
    {
        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $carrier = $shipment->getOrder()->getShippingCarrier();

            $data = array();
            foreach ($shipment->getAllTracks() as $track) {
                if ($track->getCarrierCode() == $carrier->getCarrierCode()) {
                    $data[] = array('tracking_number' => $track->getTrackNumber());
                }
            }

            if ($data) {
                if (!$carrier || !$carrier instanceof Unl_Ship_Model_Shipping_Carrier_VoidInterface || !$carrier->isVoidAvailable()) {
                    Mage::throwException($this->__('The shipping carrier does not support voiding shipments.'));
                }

                if (!$carrier->requestToVoid($data)) {
                    Mage::throwException($this->__('Failed to void a tracking number from the shipping carrier'));
                }
            }

            $shipment->unregister();

            $shipment->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $this->__('Voided and deleted a shipment for reprocessing.'));

            if (($result = $carrier->getLastVoidResult()) && $result->hasMessage()) {
                $shipment->getOrder()->addStatusHistoryComment($result->getMessage());
            }

            $this->_saveShipment($shipment);
            $this->_getSession()->addSuccess($this->__('Successfully voided and deleted shipment.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/view', array('shipment_id' => $this->getRequest()->getParam('shipment_id')));
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot void shipment.'));
            $this->_redirect('*/*/view', array('shipment_id' => $this->getRequest()->getParam('shipment_id')));
            return;
        }

        $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
    }
}
