<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap_Products_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Products
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_reconcile_nocap_products_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelNocapProductsRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvNocapProductsRefunded';
    }
}
