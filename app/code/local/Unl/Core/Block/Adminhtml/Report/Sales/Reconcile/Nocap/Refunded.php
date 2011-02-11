<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_reconcile_nocap_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelNocapRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvNocapRefunded';
}