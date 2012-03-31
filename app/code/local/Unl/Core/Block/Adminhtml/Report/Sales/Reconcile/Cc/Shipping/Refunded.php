<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Cc_Shipping_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Shipping_Refunded
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_reconcile_cc_shipping_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelCcShippingRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvCcShippingRefunded';
    }
}
