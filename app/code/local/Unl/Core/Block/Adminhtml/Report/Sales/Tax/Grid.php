<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Grid extends Mage_Adminhtml_Block_Report_Sales_Tax_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'            => Mage::helper('sales')->__('Period'),
            'index'             => 'period',
            'width'             => '100',
            'sortable'          => false,
            'period_type'       => $this->getPeriodType(),
            'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'      => Mage::helper('adminhtml')->__('Total'),
            'subtotals_label'   => Mage::helper('adminhtml')->__('SubTotal')
        ));
        
        $this->addColumn('code', array(
            'header'    => Mage::helper('sales')->__('Tax Code'),
            'index'     => 'code',
            'type'      => 'string',
            'sortable'  => false
        ));
        
        $this->addColumn('city', array(
            'header'    => Mage::helper('sales')->__('City'),
            'index'     => 'city',
            'type'      => 'string',
            'sortable'  => false
        ));
        
        $this->addColumn('county', array(
            'header'    => Mage::helper('sales')->__('County'),
            'index'     => 'county',
            'type'      => 'string',
            'sortable'  => false
        ));
        
        $this->addColumn('base_sales_amount_sum', array(
            'header'        => Mage::helper('sales')->__('Sales Amount'),
            'index'         => 'base_sales_amount_sum',
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'sortable'      => false
        ));

        $this->addColumn('percent', array(
            'header'    => Mage::helper('sales')->__('Rate'),
            'index'     => 'percent',
            'type'      => 'number',
            'width'     => '100',
            'sortable'  => false
        ));

        $this->addColumn('orders_count', array(
            'header'    => Mage::helper('sales')->__('Number of Orders'),
            'index'     => 'orders_count',
            'type'      => 'number',
            'width'     => '100',
            'sortable'  => false
        ));

        $this->addColumn('tax_base_amount_sum', array(
            'header'        => Mage::helper('sales')->__('Tax Amount'),
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'         => 'tax_base_amount_sum',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addExportType('*/*/exportTaxCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportTaxExcel', Mage::helper('reports')->__('Excel'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }
}