<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co_Products_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName = 'unl_core/report_bursar_co_products_paid';
        $this->_exportExcelUrl = '*/*/exportExcelCoProductsPaid';
        $this->_exportCsvUrl   = '*/*/exportCsvCoProductsPaid';
    }

    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addCostObjectColumns($this, false);

        return parent::_prepareColumns();
    }
}
