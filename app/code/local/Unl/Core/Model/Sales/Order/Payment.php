<?php

class Unl_Core_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    /* Overrides
     * @see Mage_Sales_Model_Order_Payment::canVoid()
     * by also checking for a capture transaction
     */
    public function canVoid(Varien_Object $document)
    {
        if (null === $this->_canVoidLookup) {
            $this->_canVoidLookup = (bool)$this->getMethodInstance()->canVoid($document);
            if ($this->_canVoidLookup) {
                $authTransaction = $this->getAuthorizationTransaction();
                $captureTransaction = $this->_lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
                $this->_canVoidLookup = (bool)$authTransaction && !(int)$authTransaction->getIsClosed() && !$captureTransaction;
            }
        }
        return $this->_canVoidLookup;
    }

	/**
     * Decide whether parent transaction may close (if the amount to credit will cover entire order)
     *
     * @param float $amountToRefund
     * @return bool
     */
    protected function _isRefundFinal($amountToRefund)
    {
        $amountToRefund = $this->_formatAmount($amountToRefund, true);
        $orderGrandTotal = $this->_formatAmount($this->getOrder()->getBaseGrandTotal(), true);
        if ($orderGrandTotal == $this->_formatAmount($this->getBaseAmountRefunded(), true) + $amountToRefund) {
            if (false !== $this->getShouldCloseParentTransaction()) {
                $this->setShouldCloseParentTransaction(true);
            }
            return true;
        }
        return false;
    }

    /* Overrides
     * @see Mage_Sales_Model_Order_Payment::refund()
     * by implementing multiple refunds per capture
     */
    public function refund($creditmemo)
    {
        $baseAmountToRefund = $this->_formatAmount($creditmemo->getBaseGrandTotal());
        $order = $this->getOrder();

        $this->_generateTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND);

        // call refund from gateway if required
        $isOnline = false;
        $gateway = $this->getMethodInstance();
        $invoice = null;
        if ($gateway->canRefund() && $creditmemo->getDoTransaction()) {
            $this->setCreditmemo($creditmemo);
            $invoice = $creditmemo->getInvoice();
            if ($invoice) {
                $isOnline = true;
                $captureTxn = $this->_lookupTransaction($invoice->getTransactionId());
                if ($captureTxn) {
                    $this->setParentTransactionId($captureTxn->getTxnId());
                }
                $this->_isRefundFinal($baseAmountToRefund);
                try {
                    $gateway->setStore($this->getOrder()->getStoreId())
                        ->processBeforeRefund($invoice, $this)
                        ->refund($this, $baseAmountToRefund)
                        ->processCreditmemo($creditmemo, $this)
                    ;
                } catch (Mage_Core_Exception $e) {
                    if (!$captureTxn) {
                        $e->setMessage(' ' . Mage::helper('sales')->__('If the invoice was created offline, try creating an offline creditmemo.'), true);
                    }
                    throw $e;
                }
            }
        }

        // update self totals from creditmemo
        $this->_updateTotals(array(
            'amount_refunded' => $creditmemo->getGrandTotal(),
            'base_amount_refunded' => $baseAmountToRefund,
            'base_amount_refunded_online' => $isOnline ? $baseAmountToRefund : null,
            'shipping_refunded' => $creditmemo->getShippingAmount(),
            'base_shipping_refunded' => $creditmemo->getBaseShippingAmount(),
        ));

        // update transactions and order state
        $transaction = $this->_addTransaction(
            Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND,
            $creditmemo,
            $isOnline
        );
        if ($invoice) {
            $message = Mage::helper('sales')->__('Refunded amount of %s online.', $this->_formatPrice($baseAmountToRefund));
        } else {
            $message = $this->hasMessage() ? $this->getMessage()
                : Mage::helper('sales')->__('Refunded amount of %s offline.', $this->_formatPrice($baseAmountToRefund));
        }
        $message = $message = $this->_prependMessage($message);
        $message = $this->_appendTransactionToMessage($transaction, $message);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $message);

        Mage::dispatchEvent('sales_order_payment_refund', array('payment' => $this, 'creditmemo' => $creditmemo));
        return $this;
    }
}
