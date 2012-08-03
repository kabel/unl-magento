<?php

class Unl_Core_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Tax
{
    /* Overrides
     * by not collecting shipping tax for auto-capture
     */
    protected function _canIncludeShipping($invoice)
    {
        $session = Mage::getSingleton('checkout/session');
        if ($session->getIsInvoiceAutoCapture()) {
            return false;
        }

        $includeShippingTax = true;
        /**
         * Check shipping amount in previous invoices
         */
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
            if ((float)$previusInvoice->getShippingAmount() && !$previusInvoice->isCanceled()) {
                $includeShippingTax = false;
            }
        }
        return $includeShippingTax;
    }
}
