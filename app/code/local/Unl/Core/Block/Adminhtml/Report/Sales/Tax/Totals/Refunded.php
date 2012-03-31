<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Totals_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Tax_Totals_Paid
{
    public function __construct()
    {
        parent::__construct();
        $this->_resourceCollectionName = 'unl_core/report_tax_totals_refunded';
    }

    protected function _getExportCsvUrl()
    {
        return '*/*/exportTotalsRefundedCsv';
    }

    protected function _getExportExcelUrl()
    {
        return '*/*/exportTotalsRefundedExcel';
    }
}
