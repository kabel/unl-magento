<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Cc_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_reconcile_cc_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelCcRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvCcRefunded';
}
