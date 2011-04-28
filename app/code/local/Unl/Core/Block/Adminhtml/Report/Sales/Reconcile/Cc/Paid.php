<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Cc_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_reconcile_cc_paid';

    protected $_exportExcelUrl = '*/*/exportExcelCcPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvCcPaid';
}
