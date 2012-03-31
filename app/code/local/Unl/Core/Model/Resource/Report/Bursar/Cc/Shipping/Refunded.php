<?php

class Unl_Core_Model_Resource_Report_Bursar_Cc_Shipping_Refunded extends Unl_Core_Model_Resource_Report_Bursar_Collection_Shipping_Refunded
{
    public function __construct()
    {
        parent::__construct();
        $this->_paymentMethodCodes = Mage::helper('unl_core/report_bursar')->getPaymentMethodCodes('cc');
    }
}
