<?php

class Unl_Core_Model_Sales_Order_Creditmemo_Total_Shipping extends Mage_Sales_Model_Order_Creditmemo_Total_Shipping
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $allowedAmount          = $order->getShippingAmount()-$order->getShippingRefunded();
        $baseAllowedAmount      = $order->getBaseShippingAmount()-$order->getBaseShippingRefunded();

        $shipping               = $order->getShippingAmount();
        $baseShipping           = $order->getBaseShippingAmount();
        $shippingInclTax        = $order->getShippingInclTax();
        $baseShippingInclTax    = $order->getBaseShippingInclTax();

        $isShippingInclTax = Mage::getSingleton('tax/config')->displaySalesShippingInclTax($order->getStoreId());

        /**
         * Check if shipping amount was specified (from invoice or another source).
         * Using has magic method to allow setting 0 as shipping amount.
         */
        if ($creditmemo->hasBaseShippingAmount()) {
            $baseShippingAmount = Mage::app()->getStore()->roundPrice($creditmemo->getBaseShippingAmount());
            if ($isShippingInclTax && $baseShippingInclTax != 0) {
                $part = $baseShippingAmount/$baseShippingInclTax;
                $shippingInclTax    = Mage::app()->getStore()->roundPrice($shippingInclTax*$part);
                $baseShippingInclTax= $baseShippingAmount;
                $baseShippingAmount = Mage::app()->getStore()->roundPrice($baseShipping*$part);
            }
            /*
             * Rounded allowed shipping refund amount is the highest acceptable shipping refund amount.
             * Shipping refund amount shouldn't cause errors, if it doesn't exceed that limit.
             * Note: ($x < $y + 0.0001) means ($x <= $y) for floats
             */
            if ($baseShippingAmount < Mage::app()->getStore()->roundPrice($baseAllowedAmount) + 0.0001) {
                /*
                 * Shipping refund amount should be equated to allowed refund amount,
                 * if it exceeds that limit.
                 * Note: ($x > $y - 0.0001) means ($x >= $y) for floats
                 */
                if ($baseShippingAmount > $baseAllowedAmount - 0.0001) {
                    $shipping     = $allowedAmount;
                    $baseShipping = $baseAllowedAmount;
                } else {
                    if ($baseShipping != 0) {
                        $shipping = $shipping * $baseShippingAmount / $baseShipping;
                    }
                    $shipping     = Mage::app()->getStore()->roundPrice($shipping);
                    $baseShipping = $baseShippingAmount;
                }
            } else {
                $baseAllowedAmount = $order->getBaseCurrency()->format($baseAllowedAmount,null,false);
                Mage::throwException(
                    Mage::helper('sales')->__('Maximum shipping amount allowed to refund is: %s', $baseAllowedAmount)
                );
            }
        } else {
            if ($baseShipping != 0) {
                $shippingInclTax    = Mage::app()->getStore()->roundPrice($shippingInclTax * $allowedAmount/$shipping);
                $baseShippingInclTax= Mage::app()->getStore()->roundPrice($baseShippingInclTax * $baseAllowedAmount/$baseShipping);
            }
            $shipping           = $allowedAmount;
            $baseShipping       = $baseAllowedAmount;
        }

        $creditmemo->setShippingAmount($shipping);
        $creditmemo->setBaseShippingAmount($baseShipping);
        $creditmemo->setShippingInclTax($shippingInclTax);
        $creditmemo->setBaseShippingInclTax($baseShippingInclTax);

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal()+$shipping);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal()+$baseShipping);
        return $this;

    }
}