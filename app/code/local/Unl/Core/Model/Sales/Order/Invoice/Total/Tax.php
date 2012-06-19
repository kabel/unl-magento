<?php

class Unl_Core_Model_Sales_Order_Invoice_Total_Tax extends Mage_Sales_Model_Order_Invoice_Total_Tax
{
    /* Extends
     * by not collecting shipping tax for auto-capture
     */
    protected function _canIncludeShipping($invoice)
    {
        $session = Mage::getSingleton('checkout/session');
        if ($session->getIsInvoiceAutoCapture()) {
            return false;
        }

        return parent::_canIncludeShipping($invoice);
    }
}
