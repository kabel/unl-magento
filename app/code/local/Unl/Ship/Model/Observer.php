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
