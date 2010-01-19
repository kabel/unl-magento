<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Grid extends Mage_Adminhtml_Block_Report_Sales_Tax_Grid
{
    protected function _prepareColumns()
    {
        $this->addColumn('code', array(
            'header'    =>Mage::helper('reports')->__('Tax'),
            'index'     =>'code',
            'type'      =>'string'
        ));
        
        $this->addColumn('sales_amount', array(
            'header'    => Mage::helper('reports')->__('Sales Amount'),
            'index'     => 'sales_amount',
            'type'      => 'currency',
            'currency_code'=>$this->getCurrentCurrencyCode(),
            'renderer'  => 'adminhtml/report_grid_column_renderer_currency',
            'width'     => '100',
            'total'     => 'sum'
        ));

        $this->addColumn('percent', array(
            'header'    =>Mage::helper('reports')->__('Rate'),
            'index'     =>'percent',
            'type'      =>'number',
            'renderer'  =>'adminhtml/report_grid_column_renderer_blanknumber',
            'width'     =>'100'
        ));

        $this->addColumn('orders', array(
            'header'    =>Mage::helper('reports')->__('Number of Orders'),
            'index'     =>'orders',
            'total'     =>'sum',
            'type'      =>'number',
            'width'     =>'100'
        ));

        $this->addColumn('tax', array(
            'header'    =>Mage::helper('reports')->__('Tax Amount'),
            'type'      =>'currency',
            'currency_code'=>$this->getCurrentCurrencyCode(),
            'index'     =>'tax',
            'total'     =>'sum',
            'renderer'  =>'adminhtml/report_grid_column_renderer_currency'
        ));

        $this->addExportType('*/*/exportTaxCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportTaxExcel', Mage::helper('reports')->__('Excel'));

        return Mage_Adminhtml_Block_Report_Grid::_prepareColumns();
    }
}