<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap_Grid_Products_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_nocap_products_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelNocapProductsRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvNocapProductsRefunded';
}