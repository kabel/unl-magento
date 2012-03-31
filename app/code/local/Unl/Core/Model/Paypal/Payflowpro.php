<?php

class Unl_Core_Model_Paypal_Payflowpro extends Mage_Paypal_Model_Payflowpro
{
    /**
     * Additional response codes
     */
    const RESPONSE_CODE_INVALID_ACCOUNT   = 23;
    const RESPONSE_CODE_INVALID_EXP_DATE  = 24;
    const RESPONSE_CODE_CSC_MISMATCH      = 114;

    protected $_cardInfoErrors = array(
        self::RESPONSE_CODE_DECLINED,
        self::RESPONSE_CODE_INVALID_ACCOUNT,
        self::RESPONSE_CODE_INVALID_EXP_DATE,
        self::RESPONSE_CODE_CSC_MISMATCH,
    );

    /* Extends
     * @see Mage_Payment_Model_Method_Abstract::canCapturePartial()
     * by first checking the config for reference_txn
     */
    public function canCapturePartial()
    {
        return ($this->getConfigData('reference_txn') || parent::canCapturePartial());
    }

    /* Extends
     * @see Mage_Payment_Model_Method_Abstract::canRefundPartialPerInvoice()
     * by first checking the config for reference_txn
     */
    public function canRefundPartialPerInvoice()
    {
        return ($this->getConfigData('reference_txn') || parent::canRefundPartialPerInvoice());
    }

    /**
     * Checks if any capture transaction exists for the payment's order
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return boolean
     */
    protected function _isOrderPartialCaptured($payment)
    {
        return $payment->lookupTransaction(false, Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE) !== false;
    }

    /* Overrides
     * @see Mage_Paypal_Model_Payflowpro::capture()
     * by implementing delayed capture and partial capture
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if ($payment->getReferenceTransactionId()) {
            $request = $this->_buildPlaceRequest($payment, $amount);
            $request->setTrxtype(self::TRXTYPE_SALE);
            $request->setOrigid($payment->getReferenceTransactionId());
        } elseif ($payment->getParentTransactionId()) {
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

    /* Extends
     * @see Mage_Paypal_Model_Payflowpro::_processErrors()
     * by throwing a specific exception for declined responses
     */
    protected function _processErrors(Varien_Object $response)
    {
        if (in_array($response->getResultCode(), $this->_cardInfoErrors)) {
            throw new Mage_Payment_Model_Info_Exception(
                Mage::helper('paypal')->__('Transaction Declined. Please review your card information to ensure it is correct and try again. If you still have problems, please call your card issuing bank to resolve.')
            );
        }

        parent::_processErrors($response);
    }
}
