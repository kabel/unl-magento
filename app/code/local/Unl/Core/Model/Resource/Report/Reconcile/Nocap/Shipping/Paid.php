<?php

class Unl_Core_Model_Resource_Report_Reconcile_Nocap_Shipping_Paid
    extends Unl_Core_Model_Resource_Report_Reconcile_Collection_Shipping_Paid
{
    public function __construct()
    {
        parent::__construct();
        $this->_paymentMethodCodes = Mage::helper('unl_core/report_bursar')->getPaymentMethodCodes('nocap');
    }
}
