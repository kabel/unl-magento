<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co_Shipping_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected function _construct()
    {
        parent::_construct();
        $this->_resourceCollectionName = 'unl_core/report_bursar_co_shipping_paid';
        $this->_exportExcelUrl = '*/*/exportExcelCoShippinPaid';
        $this->_exportCsvUrl   = '*/*/exportCsvCoShippingPaid';
    }

    protected function _prepareColumns()
    {
        Mage::helper('unl_core/report_bursar')->addCostObjectColumns($this, false, true);

        return parent::_prepareColumns();
    }
}
