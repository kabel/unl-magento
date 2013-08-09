<?php

class Unl_Ship_Model_Observer
{
    /**
     * Event handler for the <code>adminhtml_block_html_before</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();

        //Do actions based on block type

        $type = 'Mage_Adminhtml_Block_Sales_Order_Grid';
        if ($block instanceof $type) {
            if ($this->_isAllowedSalesAction('label_ship'))  {
                $block->getMassactionBlock()->addItem('unl_ship_queue', array(
                     'label' => Mage::helper('unl_ship')->__('Queue for Shipment'),
                     'url'   => $block->getUrl('*/sales_order_shipment/queueOrders'),
                ));
            }
            return;
        }
    }

    /**
     * An <i>adminhtml</i> event observer for the custom <code>shipping_carrier_request_to_shipment</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeRequestToShipment($observer)
    {
        /* @var $request Mage_Shipping_Model_Shipment_Request */
        /* @var $carrier Mage_Shipping_Model_Carrier_Abstract */
        /* @var $result  Varien_Object */
        $request = $observer->getEvent()->getRequest();
        $carrier = $observer->getEvent()->getCarrier();
        $result  = $observer->getEvent()->getResult();

        $session = Mage::getSingleton('adminhtml/session');

        $ackExpiration = $session->getOrderShipmentAckExp();
        if ($ackExpiration && $ackExpiration <= time()) {
            $session->getOrderShipmentAckExp(true);
            $session->getLastOrderShipmentAck(true);
        }

        $lastOrderAck = $session->getLastOrderShipmentAck(true);
        if ($lastOrderAck === $request->getOrderShipment()->getOrderId()) {
            return;
        }

        $pickup = Mage::getModel('unl_core/shipping_carrier_pickup');
        $replacementAddr = $pickup->getReplacementAddress($request->getStoreId());
        $replacementAddr['region_code'] = Mage::getModel('directory/region')->load($replacementAddr['region_id'])->getCode();

        if ($request->getRecipientContactCompanyName() == $replacementAddr['company']
            && $request->getRecipientAddressStreet1() == $replacementAddr['street'][0]
            && $request->getRecipientAddressStreet2() == $replacementAddr['street'][1]
            && $request->getRecipientAddressCity() == $replacementAddr['city']
            && $request->getRecipientAddressStateOrProvinceCode() == $replacementAddr['region_code']
            && strpos($request->getRecipientAddressPostalCode(), $replacementAddr['postcode']) === 0
            && $request->getRecipientAddressCountryCode() == $replacementAddr['country_id']
            && $request->getRecipientContactPhoneNumber() == $replacementAddr['telephone']
        ) {
            $result->setError(Mage::helper('unl_ship')->__('You are about to create a label for the "internal pickup" address. Are you sure this is correct?'));
            $session->setOrderShipmentAckExp(time() + (5 * 60));
            $session->setLastOrderShipmentAck($request->getOrderShipment()->getOrderId());
        }
    }

    /**
     * An event observer for the <code>sales_order_shipment_save_after</code> event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAfterSalesOrderShipmentSave($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        if ($pkgs = $shipment->getUnlPackages()) {
            foreach ($pkgs as $pkg) {
                $pkg->setShipmentId($shipment->getId());
                $pkg->save();
            }
        }
    }

    protected function _isAllowedSalesAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
