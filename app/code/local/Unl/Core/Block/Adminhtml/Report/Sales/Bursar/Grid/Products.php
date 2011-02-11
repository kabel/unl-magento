<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Products extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract
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

        $this->addColumn('merchant', array(
            'header'          => Mage::helper('sales')->__('Merchant'),
            'index'           => 'merchant',
            'totals_label'    => '',
            'subtotals_label' => Mage::helper('sales')->__('SubTotal'),
            'sortable'        => false
        ));

        $this->addColumn('items_count', array(
            'header'    => Mage::helper('sales')->__('Items'),
            'index'     => 'items_count',
            'type'      => 'number',
            'total'     => 'sum',
            'sortable'  => false
        ));

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn('total_subtotal', array(
            'header'        => Mage::helper('sales')->__('Subtotal'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_subtotal',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_tax', array(
            'header'        => Mage::helper('sales')->__('Tax'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_tax',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addColumn('total_discount', array(
            'header'        => Mage::helper('sales')->__('Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'total_discount',
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