<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap_Grid_Products_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_nocap_products_paid';

    protected $_exportExcelUrl = '*/*/exportExcelNocapProductsPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvNocapProductsPaid';
}
