<?php

class Unl_Core_Model_Paypal_Payflowpro extends Mage_Paypal_Model_Payflowpro
{
    public function canCapturePartial()
    {
        return ($this->getConfigData('reference_txn') || parent::canCapturePartial());
    }

    public function canRefundPartialPerInvoice()
    {
        return ($this->getConfigData('reference_txn') || parent::canRefundPartialPerInvoice());
    }

    /**
     * Checks to see if there is an existing capture transaction for the given order payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return bool
     */
    protected function _isOrderPartialCaptured($payment)
    {
        $collection = Mage::getModel('sales/order_payment_transaction')->getCollection()
            ->setOrderFilter($payment->getOrder())
            ->addPaymentIdFilter($payment->getId())
            ->addTxnTypeFilter(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE);
        if (count($collection)) {
            return true;
        }

        return false;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Payflowpro
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getParentTransactionId()) {
            $request = $this->_buildBasicRequest($payment);
            if (!$this->canCapturePartial()) {
                $request->setTrxtype(self::TRXTYPE_DELAYED_CAPTURE);
            } else {
                if (!$this->_isOrderPartialCaptured($payment)) { // we have no capture yet
                    $request->setTrxtype(self::TRXTYPE_DELAYED_CAPTURE);
                } else {
                    $request->setTrxtype(self::TRXTYPE_SALE);
                }
                $request->setAmt(round($amount,2));
            }
            $request->setOrigid($payment->getParentTransactionId());
        } else {
            $request = $this->_buildPlaceRequest($payment, $amount);
            $request->setTrxtype(self::TRXTYPE_SALE);
        }

        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        switch ($response->getResultCode()){
            case self::RESPONSE_CODE_APPROVED:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                break;
            case self::RESPONSE_CODE_FRAUDSERVICE_FILTER:
                $payment->setTransactionId($response->getPnref())->setIsTransactionClosed(0);
                $payment->setIsTransactionPending(true);
                $payment->setIsFraudDetected(true);
                break;
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Payflowpro
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $request = $this->_buildBasicRequest($payment);
        $request->setAmt(round($amount,2));
        $request->setTrxtype(self::TRXTYPE_CREDIT);
        $request->setOrigid($payment->getParentTransactionId());
        $response = $this->_postRequest($request);
        $this->_processErrors($response);

        if ($response->getResultCode() == self::RESPONSE_CODE_APPROVED){
            $payment->setTransactionId($response->getPnref())
                ->setIsTransactionClosed(1);
        }
        return $this;
    }
}