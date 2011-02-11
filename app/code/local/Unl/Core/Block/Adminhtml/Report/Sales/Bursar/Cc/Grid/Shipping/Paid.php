<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Grid_Shipping_Paid extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_cc_shipping_paid';

    protected $_exportExcelUrl = '*/*/exportExcelCcShippingPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvCcShippingPaid';
}