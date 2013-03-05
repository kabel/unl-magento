<?php

class Unl_Inventory_Block_Inventory_Purchase_Edit_Tab_Audit extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('invPurchaseAuditGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('unl_inventory')->__('No Audits Associated'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_inventory/audit_collection')
            ->addPurchaseFilter(Mage::registry('current_purchase')->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('type', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Type'),
            'type'      =>  'options',
            'index'     =>  'type',
            'options'   => Mage::getModel('unl_inventory/source_audittype')->toOptionHash(true),
            'width'     => '100',
        ));

        $this->addColumn('qty', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Qty'),
            'type'      =>  'number',
            'align'     =>  'center',
            'index'     =>  'qty_affected',
        ));

        $this->addColumn('created_at', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Date'),
            'type'      =>  'datetime',
            'index'     => 'created_at',
            'width'     => '150',
        ));

         $this->addColumn('note', array(
             'header'    =>  Mage::helper('unl_inventory')->__('Note'),
             'sortable'  =>  false,
             'index'     =>  'note',
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/auditGrid', array('_current' => true));
    }

    public function getRowUrl($item)
    {
        return false;
    }
}