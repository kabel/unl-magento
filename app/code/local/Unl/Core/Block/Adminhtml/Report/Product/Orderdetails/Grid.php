<?php

class Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid extends Mage_Adminhtml_Block_Report_Grid
{
    protected $_defaultFilters = array(
            'report_from' => '',
            'report_to' => '',
            'report_period' => 'day',
            'sku' => ''
        );

    /**
     * Sub report size
     *
     * @var int
     */
    protected $_subReportSize = 0;

    /**
     * Initialize Grid settings
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unl/report/grid2.phtml');
        $this->setId('gridProductsOrderDetails');
    }

    /**
     * Prepare collection object for grid
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
    protected function _prepareCollection()
    {
//        $this->setFilter('report_period', 'day');
        parent::_prepareCollection();

        if ($this->getFilter('sku')) {
            Mage::register('filter_sku', $this->getFilter('sku'));
        }

        $this->getCollection()
            ->initReport('unl_core/report_product_orderdetails_collection');
        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('sku', array(
            'header'    =>Mage::helper('reports')->__('SKU'),
            'index'     =>'sku'
        ));

        $this->addColumn('name', array(
            'header'    =>Mage::helper('reports')->__('Product Name'),
            'index'     =>'name'
        ));

        $this->addColumn('ordered_qty', array(
            'header'    =>Mage::helper('reports')->__('Quantity Ordered'),
            'width'     =>'120px',
            'align'     =>'right',
            'index'     =>'ordered_qty',
            'type'      =>'number'
        ));

        $currencyCode = $this->getCurrentCurrencyCode();
        $this->addColumn('base_price', array(
            'header'        => Mage::helper('reports')->__('Price'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_price',
        ));

        $this->addColumn('customer_firstname', array(
            'header'    =>Mage::helper('reports')->__('Customer First Name'),
            'index'     =>'customer_firstname'
        ));

        $this->addColumn('customer_lastname', array(
            'header'    =>Mage::helper('reports')->__('Customer Last Name'),
            'index'     =>'customer_lastname'
        ));

        $this->addColumn('ordernum', array(
            'header'    =>Mage::helper('reports')->__('Order #'),
            'index'     =>'ordernum'
        ));

        $this->addExportType('*/*/exportOrderdetailsCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportOrderdetailsExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}