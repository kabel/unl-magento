<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('orderaddressGrid');
        $this->setDefaultSort('created_at');
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

        $filter = $this->getRequest()->getParam($this->getVarNameFilter());
        if ($filter === '') {
            $this->getRequest()->setParam('order_ids', false);
        }

        if ($this->getParam('order_ids')) {
            $collection->addFieldToFilter('parent_id', array('in' => $this->getParam('order_ids')));
        }

        $select = Mage::helper('unl_core')->addAdminScopeFilters($collection, 'parent_id');

        if ($this->getRequest()->getParam('product')) {
            $adapter = $collection->getConnection();

            if (!$select) {
                /* @var $select Varien_Db_Select */
                $select = Mage::getModel('sales/order_item')->getCollection()->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('order_id'))
                    ->group('order_id');
                $collection->getSelect()->join(array('scope' => $select), 'main_table.parent_id = scope.order_id', array());
            }

            $select->where($adapter->prepareSqlCondition('product_id', $this->getParam('product')));
        }

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

        $this->addColumn('email', array(
            'header'    => Mage::helper('sales')->__('Email'),
            'index'     => 'email',
        ));

        $this->addColumn('company', array(
            'header'    => Mage::helper('sales')->__('Company'),
            'index'     => 'company',
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
        return $this->getUrl('*/*/orderaddressGrid', array('_current' => array('product')));
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $item->getParentId()));
    }
}
