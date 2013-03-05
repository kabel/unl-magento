<?php

class Unl_Inventory_Block_Inventory_Edit_Tab_Purchases extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('invPurchasesGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('unl_inventory')->__('No Purchase History'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_inventory/purchase_collection')
            ->addProductFilter(Mage::registry('current_product')->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Date'),
            'type'      =>  'datetime',
            'index'     => 'created_at',
            'width'     => '150',
        ));

        $this->addColumn('qty', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Qty'),
            'type'      =>  'number',
            'align'     =>  'center',
            'index'     =>  'qty',
        ));

        $this->addColumn('amount', array(
            'header'       => Mage::helper('unl_inventory')->__('Cost'),
            'type'         => 'currency',
            'currency_code' => Mage::app()->getBaseCurrencyCode(),
            'index'        => 'amount',
            'default'      => ' '
        ));

        $this->addColumn('qty_on_hand', array(
            'header'       => Mage::helper('unl_inventory')->__('Qty Remaining'),
            'type'         => 'number',
            'index'        => 'qty_on_hand',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/purchasesGrid', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('catalog/inventory/edit')) {
            return $this->getUrl('*/catalog_inventory_purchase/edit', array('id' => $item->getId()));
        }

        return false;
    }
}