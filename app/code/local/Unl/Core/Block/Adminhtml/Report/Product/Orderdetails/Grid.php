<?php

class Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('orderdetailsProductsGrid');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_core/report_product_orderdetails_collection');

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

        $this->addColumn('order_date', array(
            'header'    => Mage::helper('sales')->__('Purchased On'),
            'index'     => 'order_date',
            'type'      => 'datetime',
            'width'     => '170px',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('sales')->__('Order Status'),
            'index'     => 'order_status',
            'type'      => 'options',
            'width'     => '110px',
            'options'   => Mage::getSingleton('sales/order_config')->getStatuses(),
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
            'header'    => Mage::helper('reports')->__('Qty Ordered'),
            'width'     => '100px',
            'align'     => 'right',
            'index'     => 'qty_adjusted',
            'type'      => 'number',
        ));

        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $this->addColumn('base_price', array(
            'header'        => Mage::helper('reports')->__('Row Total'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_total',
        ));

        $this->addColumn('customer_firstname', array(
            'header'    => Mage::helper('sales')->__('Customer First Name'),
            'index'     => 'customer_firstname',
        ));

        $this->addColumn('customer_lastname', array(
            'header'    => Mage::helper('sales')->__('Customer Last Name'),
            'index'     => 'customer_lastname',
        ));

        $this->addColumn('customer_email', array(
            'header'    => Mage::helper('sales')->__('Customer Email'),
            'index'     => 'customer_email',
        ));

        $this->addExportType('*/*/exportOrderdetailsCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportOrderdetailsExcel', Mage::helper('reports')->__('Excel XML'));

        Mage::dispatchEvent('adminhtml_grid_prepare_columns', array('grid' => $this));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $item->getOrderId()));
    }
}
