<?php

class Unl_Inventory_Block_Products_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('productInventoryGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('source_store_view')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

        $collection->addAttributeToSelect('cost');
        $collection->addAttributeToSelect('price');
        $collection->addAttributeToSelect('status');

        $user = Mage::getSingleton('admin/session')->getUser();
        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $collection->addAttributeToFilter('source_store_view', array('in' => $scope));
        }

        $collection->joinAttribute('audit_inventory', 'catalog_product/audit_inventory', 'entity_id');

        /* @var $auditSelect Varien_Db_Select */
        $auditSelect = Mage::getModel('unl_inventory/audit')->getCollection()->selectProducts()->getSelect();

        /* @var $indexSelect Varien_Db_Select */
        $indexSelect = Mage::getModel('unl_inventory/index')->getCollection()->selectQtyOnHand()->getSelect();

        $configStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $auditCondition = "IF(_table_qty.use_config_manage_stock, {$configStock}, _table_qty.manage_stock) && _table_audit_inventory.value";

        /* @var $select Varien_Db_Select */
        $select = $collection->getSelect();
        $select->joinLeft(array('ia' => $auditSelect), 'e.entity_id = ia.product_id', array())
            ->joinLeft(array('ii' => $indexSelect), 'e.entity_id = ii.product_id', array())
            ->columns(array(
            	'qty_on_hand' => "IF({$auditCondition}, ii.qty, _table_qty.qty)",
                'audit_active' => "({$auditCondition})"
            ));

        $select->where("(ia.product_id IS NOT NULL OR {$auditCondition})");

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
        ));
        $this->addColumn('name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => '80px',
                'index' => 'sku',
        ));

        $this->addColumn('qty_on_hand',
            array(
                'header'=> Mage::helper('unl_inventory')->__('Qty on Hand'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'qty_on_hand',
        ));

        $store = Mage::app()->getStore();
        $this->addColumn('cost',
            array(
                'header'=> Mage::helper('catalog')->__('Cost'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'cost',
        ));

        $this->addColumn('price',
            array(
                'header'=> Mage::helper('catalog')->__('Price'),
                'type'  => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
        ));

        $this->addColumn('audit_active',
            array(
                'header'=> Mage::helper('catalog')->__('Audit Status'),
                'width' => '70px',
                'index' => 'audit_active',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'width' => '70px',
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('source_store',
            array(
                'header'=> Mage::helper('catalog')->__('Source Store'),
                'width' => '100px',
                'sortable'  => false,
                'index'     => 'source_store_view',
                'type'      => 'options',
                'options'   => Mage::getModel('unl_core/store_source_filter')->toOptionArray(),
        ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'width'     => '50px',
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Edit'),
                        'url'     => array(
                            'base'=>'*/*/edit'
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array(
            'id'=>$row->getId())
        );
    }
}
