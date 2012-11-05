<?php

class Unl_Core_Model_Sales_Order_Invoice extends Mage_Sales_Model_Order_Invoice
{
    const STATE_WRITEOFF = 4;

    /**
     * Check invoice force pay action availability
     *
     * @return bool
     */
    public function canForcePay()
    {
        return $this->getState() != self::STATE_CANCELED
            && $this->getState() != self::STATE_PAID
            && $this->getOrder()->getPayment()->getMethodInstance()->getAllowForcePay();
    }

    public function canWriteOff()
    {
        return $this->canCancel();
    }

    /* Overrides the logic of
     * @see Mage_Sales_Model_order_Invoice::getStates()
     * by adding a "write-off" state
     */
    public static function getStates()
    {
        if (is_null(self::$_states)) {
            self::$_states = array(
                self::STATE_OPEN       => Mage::helper('sales')->__('Pending'),
                self::STATE_PAID       => Mage::helper('sales')->__('Paid'),
                self::STATE_CANCELED   => Mage::helper('sales')->__('Canceled'),
                self::STATE_WRITEOFF   => Mage::helper('sales')->__('Write-Off'),
            );
        }
        return self::$_states;
    }

    /* Overrides
     * @see Mage_Sales_Model_Order_Invoice::getStateName()
     * to fix late static binding
     */
    public function getStateName($stateId = null)
    {
        if (is_null($stateId)) {
            $stateId = $this->getState();
        }

        if (is_null(self::$_states)) {
            self::getStates();
        }
        if (isset(self::$_states[$stateId])) {
            return self::$_states[$stateId];
        }
        return Mage::helper('sales')->__('Unknown State');
    }

    public function writeOff()
    {
        $this->setState(self::STATE_WRITEOFF);
        $this->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
        Mage::dispatchEvent('sales_order_invoice_cancel', array($this->_eventObject=>$this));
        return $this;
    }

    /* Overrides the logic of
     * @see Mage_Sales_Model_Order_Invoice::register()
     * by adding a "force pay" capture case (used with checkmo)
     */
    public function register()
    {
        if ($this->getId()) {
            Mage::throwException(Mage::helper('sales')->__('Cannot register existing invoice'));
        }

        foreach ($this->getAllItems() as $item) {
            if ($item->getQty()>0) {
                $item->register();
            }
            else {
                $item->isDeleted(true);
            }
        }

        $order = $this->getOrder();
        $captureCase = $this->getRequestedCaptureCase();
        if ($this->canCapture()) {
            if ($captureCase) {
                if ($captureCase == self::CAPTURE_ONLINE) {
                    $this->capture();
                }
                elseif ($captureCase == self::CAPTURE_OFFLINE) {
                    $this->setCanVoidFlag(false);
                    $this->pay();
                }
            }
        } elseif ($this->canForcePay()) {
            if ($captureCase != self::NOT_CAPTURE) {
                $this->setCanVoidFlag(false);
                $this->pay();
            } else {
                $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, '', $this->getEmailSent());
            }
        } elseif(!$order->getPayment()->getMethodInstance()->isGateway() || $captureCase == self::CAPTURE_OFFLINE) {
            if (!$order->getPayment()->getIsTransactionPending()) {
                $this->setCanVoidFlag(false);
                $this->pay();
            }
        }

        $order->setTotalInvoiced($order->getTotalInvoiced() + $this->getGrandTotal());
        $order->setBaseTotalInvoiced($order->getBaseTotalInvoiced() + $this->getBaseGrandTotal());

        $order->setSubtotalInvoiced($order->getSubtotalInvoiced() + $this->getSubtotal());
        $order->setBaseSubtotalInvoiced($order->getBaseSubtotalInvoiced() + $this->getBaseSubtotal());

        $order->setTaxInvoiced($order->getTaxInvoiced() + $this->getTaxAmount());
        $order->setBaseTaxInvoiced($order->getBaseTaxInvoiced() + $this->getBaseTaxAmount());

        $order->setHiddenTaxInvoiced($order->getHiddenTaxInvoiced() + $this->getHiddenTaxAmount());
        $order->setBaseHiddenTaxInvoiced($order->getBaseHiddenTaxInvoiced() + $this->getBaseHiddenTaxAmount());

        $order->setShippingTaxInvoiced($order->getShippingTaxInvoiced() + $this->getShippingTaxAmount());
        $order->setBaseShippingTaxInvoiced($order->getBaseShippingTaxInvoiced() + $this->getBaseShippingTaxAmount());


        $order->setShippingInvoiced($order->getShippingInvoiced() + $this->getShippingAmount());
        $order->setBaseShippingInvoiced($order->getBaseShippingInvoiced() + $this->getBaseShippingAmount());

        $order->setDiscountInvoiced($order->getDiscountInvoiced() + $this->getDiscountAmount());
        $order->setBaseDiscountInvoiced($order->getBaseDiscountInvoiced() + $this->getBaseDiscountAmount());
        $order->setBaseTotalInvoicedCost($order->getBaseTotalInvoicedCost() + $this->getBaseCost());

        $state = $this->getState();
        if (is_null($state)) {
            $this->setState(self::STATE_OPEN);
        }

        Mage::dispatchEvent('sales_order_invoice_register', array($this->_eventObject=>$this, 'order' => $order));
        return $this;
    }

    /* Overrides
     * @see Mage_Sales_Model_Order_Invoice::isLast()
     * by skipping dummy items
     */
    public function isLast()
    {
        foreach ($this->getAllItems() as $item) {
            if (!$item->getOrderItem()->isDummy() && !$item->isLast()) {
                return false;
            }
        }
        return true;
    }
}
