<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co_Shipping_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Shipping
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName = 'unl_core/report_reconcile_co_shipping_paid';
        $this->_exportExcelUrl = '*/*/exportExcelCoShippingPaid';
        $this->_exportCsvUrl   = '*/*/exportCsvCoShippingPaid';
    }

    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addCostObjectColumns($this, true, true);

        return parent::_prepareColumns();
    }
}
