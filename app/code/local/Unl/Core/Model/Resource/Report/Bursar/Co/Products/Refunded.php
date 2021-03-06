<?php

class Unl_Core_Model_Resource_Report_Bursar_Co_Products_Refunded extends Unl_Core_Model_Resource_Report_Bursar_Collection_Products_Refunded
{
    public function __construct()
    {
        parent::__construct();
        $this->_paymentMethodCodes = Mage::helper('unl_core/report_bursar')->getPaymentMethodCodes('co');
    }

    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $this->_selectedColumns += Mage::helper('unl_core/report_bursar')->getAdditionalCostObjectColumns($this);

        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        $this->_initSelectForProducts(true);
        Mage::helper('unl_core/report_bursar')->joinBillingNameToCollection($this);

        return $this;
    }
}
