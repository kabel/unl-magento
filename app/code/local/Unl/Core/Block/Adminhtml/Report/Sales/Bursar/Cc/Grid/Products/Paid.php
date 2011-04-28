<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc_Grid_Products_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_cc_products_paid';

    protected $_exportExcelUrl = '*/*/exportExcelCcProductsPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvCcProductsPaid';
}
