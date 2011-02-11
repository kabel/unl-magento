<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co_Refunded extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_reconcile_co_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelCoRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvCoRefunded';

    protected function _prepareColumns()
    {
        $this->addColumnAfter('po_number', array(
            'header'          => Mage::helper('sales')->__('Cost Object'),
            'index'           => 'po_number',
            'totals_label'    => '',
            'subtotals_label' => '',
            'sortable'        => false
        ), 'order_num');

        return parent::_prepareColumns();
    }
}