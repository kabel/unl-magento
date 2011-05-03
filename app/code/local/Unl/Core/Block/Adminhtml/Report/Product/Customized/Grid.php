<?php

class Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_resourceCollectionName  = 'unl_core/report_product_customized_collection';
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
     * @return Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid
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
            ->addSkuFilter($filterData->getData('sku'))
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
     * @return Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid
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

        $this->addColumn('sku', array(
            'header'    => Mage::helper('reports')->__('SKU'),
            'index'     => 'sku',
            'sortable'  => false,
            'filter'    => false
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('reports')->__('Product Name'),
            'index'     => 'name',
            'sortable'  => false,
        ));

        $this->addColumn('ordered_qty', array(
            'header'    => Mage::helper('reports')->__('Qty Ordered'),
            'width'     => '120px',
            'align'     => 'right',
            'index'     => 'qty_ordered',
            'type'      => 'number',
            'sortable'  => false,
        ));

        $currencyCode = $this->getCurrentCurrencyCode();
        $this->addColumn('base_price', array(
            'header'        => Mage::helper('reports')->__('Price'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_price',
            'sortable'  => false,
        ));

        $this->addColumn('customer_firstname', array(
            'header'    => Mage::helper('reports')->__('Customer First Name'),
            'index'     => 'customer_firstname',
            'sortable'  => false,
        ));

        $this->addColumn('customer_lastname', array(
            'header'    =>Mage::helper('reports')->__('Customer Last Name'),
            'index'     =>'customer_lastname',
            'sortable'  => false,
        ));

        $this->addColumn('ordernum', array(
            'header'    => Mage::helper('reports')->__('Order #'),
            'index'     => 'ordernum',
            'sortable'  => false,
            'renderer'  => 'unl_core/adminhtml_report_product_orderdetails_grid_renderer_action'
        ));

        $this->addColumn('product_options', array(
            'header'    => Mage::helper('reports')->__('Options'),
            'index'     => 'product_options',
            'sortable'  => false,
            'renderer'  => 'unl_core/adminhtml_report_product_customized_grid_renderer_table'
        ));

        $this->addExportType('*/*/exportCustomizedCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCustomizedExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return false;
    }

    protected function _exportCsvItem(Varien_Object $item, Varien_Io_File $adapter)
    {
        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $columnValue = $column->getRowFieldExport($item);
                if (!is_array($columnValue)) {
                    $columnValue = array($columnValue);
                }
                foreach ($columnValue as $field) {
                    $row[] = $field;
                }
            }
        }
        $adapter->streamWriteCsv($row);
    }

    protected function _exportExcelItem(Varien_Object $item, Varien_Io_File $adapter, $parser = null)
    {
        if (is_null($parser)) {
            $parser = new Varien_Convert_Parser_Xml_Excel();
        }

        $row = array();
        foreach ($this->_columns as $column) {
            if (!$column->getIsSystem()) {
                $columnValue = $column->getRowFieldExport($item);
                if (!is_array($columnValue)) {
                    $columnValue = array($columnValue);
                }
                foreach ($columnValue as $field) {
                    $row[] = $field;
                }
            }
        }
        $data = $parser->getRowXml($row);
        $adapter->streamWrite($data);
    }
}
