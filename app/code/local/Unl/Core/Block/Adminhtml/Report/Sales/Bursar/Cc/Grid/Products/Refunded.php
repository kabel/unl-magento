<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Grid_Products_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_cc_products_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelCcProductsRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvCcProductsRefunded';
}