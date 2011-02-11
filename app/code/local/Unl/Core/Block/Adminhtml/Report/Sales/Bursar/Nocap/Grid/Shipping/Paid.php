<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap_Grid_Shipping_Paid extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_nocap_shipping_paid';

    protected $_exportExcelUrl = '*/*/exportExcelNocapShippingPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvNocapShippingPaid';
}