<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Tax_Reconcile_Paid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_collectionClassName;

    public function __construct()
    {
        parent::__construct();
        $this->setId('salesTaxReconcilePaidGrid');
        $this->_collectionClassName = 'unl_core/report_tax_reconcile_paid';
        $this->setDefaultSort('period');
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
        $this->addColumn('real_order_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'width'     => '80px',
            'type'      => 'text',
            'index'     => 'ordernum',
            'renderer'  => 'unl_core/adminhtml_report_product_orderdetails_grid_renderer_action'
        ));

        $this->addColumn('period', array(
            'header'    => Mage::helper('sales')->__('Processed On'),
            'index'     => 'period',
            'width'     => '100px',
            'type'      => 'datetime',
        ));

        $this->addColumn('payment_method', array(
            'header'    => Mage::helper('sales')->__('Payment Method'),
            'index'     => 'method',
            'type'      => 'options',
            'options'   => Mage::helper('unl_core')->getActivePaymentMethodOptions(false),
        ));

        $this->addColumn('code', array(
            'header'    => Mage::helper('sales')->__('Tax'),
            'index'     => 'code',
            'type'      => 'string',
        ));

        $this->addColumn('city', array(
            'header'    => Mage::helper('sales')->__('City'),
            'index'     => 'city',
            'type'      => 'string',
        ));

        $this->addColumn('county', array(
            'header'    => Mage::helper('sales')->__('County'),
            'index'     => 'county',
            'type'      => 'string',
        ));

        $this->addColumn('percent', array(
            'header'    => Mage::helper('sales')->__('Rate'),
            'index'     => 'percent',
            'type'      => 'number',
            'width'     => '100px',
        ));

        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $this->addColumn('base_sale_amount', array(
            'header'        => Mage::helper('sales')->__('Sales Amount'),
            'index'         => 'base_sale_amount',
            'type'          => 'currency',
            'currency_code' => $currencyCode,
        ));

        $this->addColumn('base_real_amount', array(
            'header'        => Mage::helper('sales')->__('Tax Amount'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_real_amount',
        ));

        $this->addExportType($this->_getCsvUrl(), Mage::helper('sales')->__('CSV'));
        $this->addExportType($this->_getExcelUrl(), Mage::helper('sales')->__('Excel XML'));

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
        return $this->getUrl('*/sales_order/view', array('order_id' => $item->getOrderId()));
    }
}
