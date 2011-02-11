<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Grid_Shipping_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping_Refunded
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_cc_shipping_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelCcShippingRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvCcShippingRefunded';
}