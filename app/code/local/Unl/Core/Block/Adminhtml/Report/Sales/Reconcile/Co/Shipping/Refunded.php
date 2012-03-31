<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co_Shipping_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Shipping_Refunded
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName = 'unl_core/report_reconcile_co_shipping_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelCoShippingRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvCoShippingRefunded';
    }

    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addCostObjectColumns($this, true, true);

        return parent::_prepareColumns();
    }
}
