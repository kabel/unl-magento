<?php

class Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customizedProductsGrid');
        $this->setDefaultSort('order_date');
        $this->setDefaultDir('desc');

        // This collection cannot be accurately paged
        $this->setDefaultLimit(1000);
        $this->setPagerVisibility(false);

        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_core/report_product_customized_collection');

        $filter = $this->getParam($this->getVarNameFilter(), null);
        if (empty($filter)) {
            $collection->addFieldToFilter('item_id', array('null'));
        }

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
            'index'     => 'name',
            'sortable'  => false,
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
            'header'        => Mage::helper('reports')->__('Price'),
            'type'          => 'currency',
            'currency_code' => $currencyCode,
            'index'         => 'base_price',
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

        $this->addColumn('product_options', array(
            'header'    => Mage::helper('reports')->__('Options'),
            'index'     => 'product_options',
            'sortable'  => false,
            'renderer'  => 'unl_core/adminhtml_report_product_customized_grid_renderer_table'
        ));

        $this->addExportType('*/*/exportCustomizedCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCustomizedExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getRowUrl($item)
    {
        return $this->getUrl('*/sales_order/view', array('order_id' => $item->getOrderId()));
    }

    /* Overrides
     * @see Mage_Adminhtml_Block_Widget_Grid::_exportCsvItem()
     * by adding support for an array of row column data
     */
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

    /* Overrides
     * @see Mage_Adminhtml_Block_Widget_Grid::_exportExcelItem()
     * by adding support for an array of row column data
     */
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
