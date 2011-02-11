<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Shipping extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract
{
    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'        => Mage::helper('sales')->__('Period'),
            'index'         => 'period',
            'width'         => 100,
            'sortable'      => false,
            'period_type'   => $this->getPeriodType(),
            'renderer'      => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'  => Mage::helper('sales')->__('Total'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('sales')->__('Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('total_tax', array(
            'header'        => Mage::helper('sales')->__('Tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_tax',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_revenue', array(
            'header'        => Mage::helper('sales')->__('Revenue'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_revenue',
            'total'         => 'sum',
            'sortable'      => false
        ));

        return parent::_prepareColumns();
    }
}