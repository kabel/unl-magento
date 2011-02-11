<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap_Paid extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_reconcile_nocap_paid';

    protected $_exportExcelUrl = '*/*/exportExcelNocapPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvNocapPaid';
}