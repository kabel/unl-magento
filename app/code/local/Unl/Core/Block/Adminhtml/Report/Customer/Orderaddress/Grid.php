<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_customer_orderaddress_collection';
    protected $_columnGroupBy = 'period';

    /**
     * Initialize Grid settings
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(false);
        $this->setCountSubTotals(false);
    }

    /**
     * Prepare collection object for grid
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
        }

        $resourceCollection = Mage::getResourceModel($this->getResourceCollectionName())
            ->setPeriod($filterData->getData('period_type'))
            ->setDateRange($filterData->getData('from', null), $filterData->getData('to', null))
            ->addStoreFilter(explode(',', $filterData->getData('store_ids')))
            ->setAggregatedColumns($this->_getAggregatedColumns());

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }

        if ($filterData->getData('show_empty_rows', false)) {
            Mage::helper('reports')->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('from', null),
                $filterData->getData('to', null),
                $filterData->getData('period_type')
            );
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid
     */
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

        $this->addColumn('firstname', array(
            'header'    =>Mage::helper('reports')->__('First Name'),
            'index'     =>'firstname',
            'sortable'  => false,
        ));

        $this->addColumn('lastname', array(
            'header'    =>Mage::helper('reports')->__('Last Name'),
            'index'     =>'lastname',
            'sortable'  => false,
        ));

        $this->addColumn('address', array(
            'header'    =>Mage::helper('reports')->__('Address'),
            'index'     =>'entity_id',
            'sortable'  => false,
            'renderer'  => 'unl_core/adminhtml_report_customer_orderaddress_grid_renderer_address'
        ));

        $this->addColumn('ordernum', array(
            'header'    =>Mage::helper('reports')->__('Order #'),
            'index'     =>'ordernum',
            'sortable'  => false,
            'renderer'  => 'unl_core/adminhtml_report_product_orderdetails_grid_renderer_action'
        ));

        $this->addExportType('*/*/exportOrderaddressCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportOrderaddressExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return false;
    }
}
