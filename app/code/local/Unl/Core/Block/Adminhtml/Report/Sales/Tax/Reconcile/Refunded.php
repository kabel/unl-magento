<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Reconcile_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Tax_Reconcile_Paid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('salesTaxReconcileRefundedGrid');
        $this->_collectionClassName = 'unl_core/report_tax_reconcile_refunded';
    }

    protected function _getCsvUrl()
    {
        return '*/*/exportReconcileRefundedCsv';
    }

    protected function _getExcelUrl()
    {
        return '*/*/exportReconcileRefundedExcel';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/reconcileRefundedGrid', array('_current'=>true));
    }
}
