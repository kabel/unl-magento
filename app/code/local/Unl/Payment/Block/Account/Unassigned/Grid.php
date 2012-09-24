<?php

class Unl_Payment_Block_Account_Unassigned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('account_unassigned_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * Retrieve Products Collection
     *
     * @return Unl_Payment_Block_Account_Grid_Products
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('attribute_set_id')
            ->addAttributeToSelect('type_id')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('visibility')
            ->addAttributeToSelect('source_store_view')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');

        $collection->addAttributeToFilter('unl_payment_account', array(array('eq' => ''), array('null' => true)), 'left');

        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);

        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepeare columns for grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        /*$this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
                'width' => 50,
                'sortable'  => true,
                'type'  => 'number',
                'index' => 'entity_id',
        ));*/
        $this->addColumn('product_name',
            array(
                'header'=> Mage::helper('catalog')->__('Name'),
                'index' => 'name',
        ));

        $this->addColumn('type',
            array(
                'header'    => Mage::helper('catalog')->__('Type'),
                'width'     => 100,
                'index'     => 'type_id',
                'type'      => 'options',
                'options'   => Mage::getSingleton('catalog/product_type')->getOptionArray(),
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'    => Mage::helper('catalog')->__('Attrib. Set Name'),
                'width'     => 100,
                'index'     => 'attribute_set_id',
                'type'      => 'options',
                'options'   => $sets,
        ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'width' => 80,
                'index' => 'sku',
        ));

        $this->addColumn('price',
            array(
                'header'        => Mage::helper('catalog')->__('Price'),
                'type'          => 'price',
                'currency_code' => Mage::app()->getStore()->getBaseCurrency()->getCode(),
                'index'         => 'price',
        ));

        $this->addColumn('visibility',
            array(
                'header'    => Mage::helper('catalog')->__('Visibility'),
                'width'     => 100,
                'index'     => 'visibility',
                'type'      => 'options',
                'options'   => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('status',
            array(
                'header'    => Mage::helper('catalog')->__('Status'),
                'width'     => 70,
                'index'     => 'status',
                'type'      => 'options',
                'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
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

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/unassignedGrid', array('_current'=>true));
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array(
            'id' => $row->getId()
        ));
    }
}
