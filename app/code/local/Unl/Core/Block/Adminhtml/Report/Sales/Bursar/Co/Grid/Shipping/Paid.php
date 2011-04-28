<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co_Grid_Shipping_Paid
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping
{
    protected $_resourceCollectionName  = 'unl_core/report_bursar_co_shipping_paid';

    protected $_exportExcelUrl = '*/*/exportExcelCoShippingPaid';
    protected $_exportCsvUrl   = '*/*/exportCsvCoShippingPaid';

    protected function _prepareColumns()
    {
        $this->addColumnAfter('po_number', array(
            'header'          => Mage::helper('sales')->__('Cost Object'),
            'index'           => 'po_number',
            'totals_label'    => '',
            'subtotals_label' => Mage::helper('sales')->__('SubTotal'),
            'sortable'        => false
        ), 'period');

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
