<?php

class Unl_Core_Model_Sales_Order_Invoice_Total_Shipping extends Mage_Sales_Model_Order_Invoice_Total_Shipping
{
    /* Overrides
     * @see Mage_Sales_Model_Order_Invoice_Total_Shipping::collect()
     * to not include shipping in an auto-capture invoice
     */
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setShippingAmount(0);
        $invoice->setBaseShippingAmount(0);

        $session = Mage::getSingleton('checkout/session');
        if ($session->getIsInvoiceAutoCapture()) {
            return $this;
        }

        $orderShippingAmount        = $invoice->getOrder()->getShippingAmount();
        $baseOrderShippingAmount    = $invoice->getOrder()->getBaseShippingAmount();
        $shippingInclTax            = $invoice->getOrder()->getShippingInclTax();
        $baseShippingInclTax        = $invoice->getOrder()->getBaseShippingInclTax();
        if ($orderShippingAmount) {
            /**
             * Check shipping amount in previus invoices
             */
            foreach ($invoice->getOrder()->getInvoiceCollection() as $previusInvoice) {
                if ((float)$previusInvoice->getShippingAmount() && !$previusInvoice->isCanceled()) {
                    return $this;
                }
            }
            $invoice->setShippingAmount($orderShippingAmount);
            $invoice->setBaseShippingAmount($baseOrderShippingAmount);
            $invoice->setShippingInclTax($shippingInclTax);
            $invoice->setBaseShippingInclTax($baseShippingInclTax);

            $invoice->setGrandTotal($invoice->getGrandTotal()+$orderShippingAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal()+$baseOrderShippingAmount);
        }
        return $this;
    }
}
