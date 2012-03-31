<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap_Grid_Shipping_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping_Refunded
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_bursar_nocap_shipping_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelNocapShippingRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvNocapShippingRefunded';
    }
}
