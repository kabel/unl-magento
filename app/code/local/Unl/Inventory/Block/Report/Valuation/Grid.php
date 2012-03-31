<?php

class Unl_Inventory_Block_Report_Valuation_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize Grid Properties
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('valuationReportGrid');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
    }

    /**
     * Prepare Search Report collection for grid
     *
     * @return Mage_Adminhtml_Block_Report_Search_Grid
     */
    protected function _prepareCollection()
    {
        /* @var $collection Unl_Inventory_Model_Resource_Products_Collection */
        $collection = Mage::getResourceModel('unl_inventory/products_collection');

        $storeIds = null;
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        }

        Mage::helper('unl_core')->addProductAdminScopeFilters($collection, $storeIds);

        $collection->joinValuation();

        $collection->addFieldToFilter('audit_active', 1);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid columns
     *
     * @return Mage_Adminhtml_Block_Report_Search_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header' => Mage::helper('catalog')->__('ID'),
                'width'  => '50px',
                'type'   => 'number',
                'index'  => 'entity_id',
                'filter' => false,
        ));

        $this->addColumn('sku',
            array(
                'header' => Mage::helper('catalog')->__('SKU'),
                'width'  => '80px',
                'index'  => 'sku',
        ));

        $this->addColumn('name',
            array(
                'header' => Mage::helper('catalog')->__('Name'),
                'index'  => 'name',
        ));

        $this->addColumn('qty',
            array(
                'header' => Mage::helper('unl_inventory')->__('Qty on Hand'),
                'width'  => '100px',
                'type'   => 'number',
                'index'  => 'qty',
        ));

        $store = Mage::app()->getStore();
        $this->addColumn('value',
            array(
                'header' => Mage::helper('unl_inventory')->__('Value'),
                'type'   => 'currency',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'  => 'value',
        ));

        $this->addColumn('avg_cost',
            array(
                'header' => Mage::helper('unl_inventory')->__('Avg Cost'),
                'type'   => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'  => 'avg_cost',
        ));

        $this->addColumn('price',
            array(
                'header' => Mage::helper('catalog')->__('Price'),
                'type'   => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index'  => 'price',
        ));


        $this->addExportType('*/*/exportValuationCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportValuationExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    /**
     * Retrieve Row Click callback URL
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/index/edit', array('id' => $row->getId()));
    }
}
