<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co_Grid_Products_Refunded
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_co_products_refunded';

    protected $_exportExcelUrl = '*/*/exportExcelCoProductsRefunded';
    protected $_exportCsvUrl   = '*/*/exportCsvCoProductsRefunded';

    protected function _prepareColumns()
    {
        $this->addColumnAfter('po_number', array(
            'header'          => Mage::helper('sales')->__('Cost Object'),
            'index'           => 'po_number',
            'totals_label'    => '',
            'subtotals_label' => '',
            'sortable'        => false
        ), 'merchant');

        $this->addColumnAfter('order_num', array(
            'header'          => Mage::helper('sales')->__('Order #'),
            'index'           => 'order_num',
            'totals_label'    => '',
            'subtotals_label' => '',
            'sortable'        => false
        ), 'po_number');

        return parent::_prepareColumns();
    }
}
