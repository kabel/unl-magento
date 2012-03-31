<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('orderaddressGrid');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection object for grid
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_core/report_customer_orderaddress_collection');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'    => Mage::helper('sales')->__('Order #'),
            'width'     => '80px',
            'type'      => 'text',
            'index'     => 'ordernum',
            'order_index' => 'parent_id',
            'renderer'  => 'unl_core/adminhtml_report_product_orderdetails_grid_renderer_action'
        ));

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('sales')->__('Purchased On'),
            'index'     => 'order_date',
            'type'      => 'datetime',
            'width'     => '170px',
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('sales')->__('First Name'),
            'index'     => 'firstname',
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('sales')->__('Last Name'),
            'index'     => 'lastname',
        ));

        $this->addColumn('address', array(
            'header'    => Mage::helper('sales')->__('Billing Address'),
            'index'     => 'entity_id',
            'sortable'  => false,
            'filter'    => false,
            'renderer'  => 'unl_core/adminhtml_report_customer_orderaddress_grid_renderer_address'
        ));

        $this->addExportType('*/*/exportOrderaddressCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportOrderaddressExcel', Mage::helper('sales')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/orderaddressGrid');
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $item->getParentId()));
    }
}
