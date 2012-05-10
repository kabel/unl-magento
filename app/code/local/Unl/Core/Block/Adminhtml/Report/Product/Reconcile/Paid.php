<?php

class Unl_Core_Block_Adminhtml_Report_Product_Reconcile_Paid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_collectionClassName;

    public function __construct()
    {
        parent::__construct();
        $this->setId('productReconcilePaidGrid');
        $this->_collectionClassName = 'unl_core/report_product_reconcile_paid';
        $this->setDefaultSort('paid_date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_collectionClassName);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('parent_number', array(
            'header'    => Mage::helper('sales')->__('Entity #'),
            'width'     => '80px',
            'type'      => 'text',
            'index'     => 'parent_number',
        ));

        $this->addColumn('paid_date', array(
            'header'    => Mage::helper('sales')->__('Processed On'),
            'index'     => 'paid_date',
            'width'     => '160px',
            'type'      => 'datetime',
        ));

        $this->addColumn('payment_method', array(
            'header'    => Mage::helper('sales')->__('Payment Method'),
            'index'     => 'payment_method',
            'type'      => 'options',
            'options'   => Mage::helper('unl_core')->getActivePaymentMethodOptions(false),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('sales')->__('SKU'),
            'type'      => 'text',
            'index'     => 'sku',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('sales')->__('Product Name'),
            'tpye'      => 'text',
            'index'     => 'name',
        ));

        $this->addColumn('source_store', array(
            'header'    => Mage::helper('sales')->__('Source Store'),
            'width'     => '100px',
            'index'     => 'source_store_view',
            'type'      => 'options',
            'options'   => Mage::getModel('unl_core/store_source_filter')->toOptionArray(),
        ));

        $this->addColumn('qty', array(
            'header'    => Mage::helper('reports')->__('Qty'),
            'width'     => '100px',
            'align'     => 'right',
            'index'     => 'qty',
            'type'      => 'number',
        ));

        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $this->addColumn('base_price', array(
            'header'        => Mage::helper('reports')->__('Price'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_price',
        ));

        $this->addColumn('base_discount', array(
            'header'        => Mage::helper('reports')->__('Discount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_discount_amount',
        ));

        $this->addColumn('base_row_gross', array(
            'header'        => Mage::helper('reports')->__('Total Gross'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_row_gross',
        ));

        $this->addExportType($this->_getCsvUrl(), Mage::helper('sales')->__('CSV'));
        $this->addExportType($this->_getExcelUrl(), Mage::helper('sales')->__('Excel XML'));

        Mage::dispatchEvent('adminhtml_grid_prepare_columns', array('grid' => $this));

        return parent::_prepareColumns();
    }

    protected function _getCsvUrl()
    {
        return '*/*/exportReconcilePaidCsv';
    }

    protected function _getExcelUrl()
    {
        return '*/*/exportReconcilePaidExcel';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/reconcilePaidGrid', array('_current'=>true));
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_invoice/view', array('invoice_id' => $item->getParentId()));
    }
}
