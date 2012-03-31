<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co_Products_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Products
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName  = 'unl_core/report_reconcile_co_products_refunded';
        $this->_exportExcelUrl = '*/*/exportExcelCoProductsRefunded';
        $this->_exportCsvUrl   = '*/*/exportCsvCoProductsRefunded';
    }

    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addCostObjectColumns($this, true);

        return parent::_prepareColumns();
    }
}
