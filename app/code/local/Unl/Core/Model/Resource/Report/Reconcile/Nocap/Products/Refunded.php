<?php

class Unl_Core_Model_Resource_Report_Reconcile_Nocap_Products_Refunded
    extends Unl_Core_Model_Resource_Report_Reconcile_Collection_Products_Refunded
{
    public function __construct()
    {
        parent::__construct();
        $this->_paymentMethodCodes = Mage::helper('unl_core/report_bursar')->getPaymentMethodCodes('nocap');
    }
}
