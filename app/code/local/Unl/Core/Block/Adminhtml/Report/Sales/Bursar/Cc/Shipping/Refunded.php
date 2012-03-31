<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Shipping_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping_Refunded
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_bursar_cc_shipping_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelCcShippingRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvCcShippingRefunded';
    }
}
