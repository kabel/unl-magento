<?php

class Unl_Core_Helper_Adminhtml_Sales_Workflow extends Mage_Core_Helper_Abstract
{
    /**
     * Check if capture operation is allowed in ACL
     * @return bool
     */
    public function isCaptureAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/capture');
    }

    /**
     * Retrieve order invoice availabilty without state checks
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function canInvoice($order)
    {
        if ($order->getPayment()->canCapture()) {
            foreach ($order->getAllItems() as $item) {
                /* @var $item Mage_Sales_Model_Order_Item */
                if ($item->getQtyToInvoice() > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns if the invoice/shipment type for any items don't match
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return boolean
     */
    public function hasInvoiceShipmentTypeMismatch($shipment)
    {
        foreach ($shipment->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Shipment_Item */
            if ($item->getOrderItem()->isChildrenCalculated() && !$item->getOrderItem()->isShipSeparately()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retruns if the order has an invoice that needs to be captured
     *
     * @param Mage_Sales_Model_Order $order
     * @return boolean
     */
    public function hasInvoiceNeedsCapture($order)
    {
        foreach ($order->getInvoiceCollection() as $invoice) {
            /* @var $invoice Mage_Sales_Model_Order_Invoice */
            if ($invoice->canCapture()) {
                return true;
            }
        }

        return false;
    }
}
