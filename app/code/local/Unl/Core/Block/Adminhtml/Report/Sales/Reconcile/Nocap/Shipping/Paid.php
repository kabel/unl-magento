<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap_Shipping_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Shipping
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_reconcile_nocap_shipping_paid';
        $this->_exportExcelUrl = '*/*/exportExcelNocapShippingPaid';
        $this->_exportCsvUrl   = '*/*/exportCsvNocapShippingPaid';
    }
}
