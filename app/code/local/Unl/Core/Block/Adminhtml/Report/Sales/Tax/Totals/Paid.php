<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Totals_Paid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';

    public function __construct()
    {
        parent::__construct();
        $this->_resourceCollectionName = 'unl_core/report_tax_totals_paid';
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    protected function _prepareColumns()
    {
        $this->addColumn('period', array(
            'header'            => Mage::helper('sales')->__('Period'),
            'index'             => 'period',
            'width'             => '100',
            'sortable'          => false,
            'period_type'       => $this->getPeriodType(),
            'renderer'          => 'adminhtml/report_sales_grid_column_renderer_date',
            'totals_label'      => Mage::helper('sales')->__('Total'),
            'subtotals_label'   => Mage::helper('sales')->__('Subtotal'),
            'html_decorators' => array('nobr'),
        ));

        $this->addColumn('code', array(
            'header'    => Mage::helper('sales')->__('Tax'),
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
            'total'     => 'sum',
            'type'      => 'number',
            'width'     => '100',
            'sortable'  => false
        ));

        if ($this->getFilterData()->getStoreIds()) {
            $this->setStoreIds(explode(',', $this->getFilterData()->getStoreIds()));
        }

        $this->addColumn('base_sales_amount_sum', array(
            'header'        => Mage::helper('sales')->__('Sales Amount'),
            'index'         => 'base_sales_amount_sum',
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'sortable'      => false
        ));

        $this->addColumn('tax_base_amount_sum', array(
            'header'        => Mage::helper('sales')->__('Tax Amount'),
            'type'          => 'currency',
            'currency_code' => $this->getCurrentCurrencyCode(),
            'index'         => 'tax_base_amount_sum',
            'total'         => 'sum',
            'sortable'      => false
        ));

        $this->addExportType($this->_getExportCsvUrl(), Mage::helper('adminhtml')->__('CSV'));
        $this->addExportType($this->_getExportExcelUrl(), Mage::helper('adminhtml')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _getExportCsvUrl()
    {
        return '*/*/exportTotalsPaidCsv';
    }

    protected function _getExportExcelUrl()
    {
        return '*/*/exportTotalsPaidExcel';
    }
}
