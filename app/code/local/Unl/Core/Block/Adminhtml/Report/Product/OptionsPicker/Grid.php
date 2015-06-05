<?php

class Unl_Core_Block_Adminhtml_Report_Product_OptionsPicker_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('optionsPickerGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('product_filter');

    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('type_id');

        $collection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner');

        /* @var $orderItemSelect Zend_Db_Select */
        $orderItemSelect = Mage::getModel('sales/order_item')->getCollection()->getSelect();
        $orderItemSelect->group('product_id');
        $orderItemSelect->columns(array('ordered_product' => 'product_id'));
        $collection->getSelect()->join(array('oi' => $orderItemSelect), 'e.entity_id=oi.ordered_product', array());

        $collection->addFieldToFilter('has_options', 1);

        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);

        $this->setCollection($collection);

        parent::_prepareCollection();

        $this->removeColumn('entity_id');

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('entity_id',
            array(
                'header'=> Mage::helper('catalog')->__('ID'),
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
                'index' => 'type_id',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_type')->getOptionArray(),
            ));

        $this->addColumn('sku',
            array(
                'header'=> Mage::helper('catalog')->__('SKU'),
                'index' => 'sku',
            ));


        $this->addColumn('status',
            array(
                'header'=> Mage::helper('catalog')->__('Status'),
                'index' => 'status',
                'type'  => 'options',
                'options' => Mage::getSingleton('catalog/product_status')->getOptionArray(),
            ));

        $this->addColumn('action',
            array(
                'header'    => Mage::helper('catalog')->__('Action'),
                'type'      => 'action',
                'getter'     => 'getId',
                'actions'   => array(
                    array(
                        'caption' => Mage::helper('catalog')->__('Report'),
                        'url'     => array(
                            'base'=>'*/*/options',
                        ),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'entity_id',
            ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/optionsPickerGrid');
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/options', array(
            'id'=>$row->getId())
        );
    }
}