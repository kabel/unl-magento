<?php

class Unl_Core_Model_Resource_Report_Reconcile_Co_Products_Paid
    extends Unl_Core_Model_Resource_Report_Reconcile_Collection_Products_Paid
{
    public function __construct()
    {
        parent::__construct();
        $this->_paymentMethodCodes = Mage::helper('unl_core/report_bursar')->getPaymentMethodCodes('co');
    }

    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $this->_selectedColumns += Mage::helper('unl_core/report_bursar')->getAdditionalCostObjectColumns($this, true);

        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        Mage::helper('unl_core/report_bursar')->joinBillingNameToCollection($this);

        return $this;
    }
}
