<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap_Shipping_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_bursar_nocap_shipping_paid';
        $this->_exportExcelUrl = '*/*/exportExcelNocapShippingPaid';
        $this->_exportCsvUrl   = '*/*/exportCsvNocapShippingPaid';
    }
}
