<?php

class Unl_Inventory_Block_Inventory_Edit_Tab_Audit extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('invAuditGrid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('desc');

        $this->setUseAjax(true);

        $this->setEmptyText(Mage::helper('unl_inventory')->__('No Audit Trail Found'));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_inventory/audit_collection')
            ->addProductFilter(Mage::registry('current_product'))
            ->addCostPerItem();

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
            'index'     =>  'qty',
        ));

        $this->addColumn('amount', array(
            'header'       => Mage::helper('unl_inventory')->__('Cost'),
            'type'         => 'currency',
            'currencyCode' => Mage::app()->getBaseCurrencyCode(),
            'index'        => 'amount',
            'default'      => ' '
        ));

        $this->addColumn('cost_per_item', array(
            'header'       => Mage::helper('unl_inventory')->__('Cost per Item'),
            'type'         => 'currency',
            'currencyCode' => Mage::app()->getBaseCurrencyCode(),
            'index'        => 'cost_per_item',
            'default'      => ' '
        ));

        $this->addColumn('created_at', array(
            'header'    =>  Mage::helper('unl_inventory')->__('Date'),
            'type'      =>  'datetime',
            'index'     => 'created_at',
            'width'     => '150',
        ));

         $this->addColumn('note', array(
             'header'    =>  Mage::helper('unl_inventory')->__('Note'),
//             'filter'    =>  false,
             'sortable'  =>  false,
             'index'     =>  'note',
        ));

        $this->addExportType('*/*/exportAuditCsv', Mage::helper('unl_inventory')->__('CSV'));
        $this->addExportType('*/*/exportAuditXml', Mage::helper('unl_inventory')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/auditGrid', array('_current' => true));
    }
}