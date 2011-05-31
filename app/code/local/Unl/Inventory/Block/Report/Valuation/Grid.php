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
        /* @var $collection Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->joinField('qty_order',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

        $collection->joinAttribute('audit_inventory', 'catalog_product/audit_inventory', 'entity_id');

        if ($this->getRequest()->getParam('store')) {
            $collection->addAttributeToFilter('source_store_view', $this->getRequest()->getParam('store'));
        }

        /* @var $indexSelect Varien_Db_Select */
        $indexSelect = Mage::getModel('unl_inventory/index')->getCollection()->selectValuation()->getSelect();
        $collection->joinTable(array('iv' => $indexSelect),
        	'product_id=entity_id',
            array('qty' => 'qty', 'value' => 'value', 'avg_cost' => 'avg_cost'), null, 'left'
        );

        $collection->addExpressionAttributeToSelect('audit_active',
            $this->_getSqlAuditCondition(),
            array()
        );
        $collection->addFieldToFilter('audit_active', true);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _getSqlAuditCondition()
    {
        $configStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $auditCondition = "IF(_table_qty_order.use_config_manage_stock, {$configStock}, _table_qty_order.manage_stock) && _table_audit_inventory.value";

        return $auditCondition;
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
