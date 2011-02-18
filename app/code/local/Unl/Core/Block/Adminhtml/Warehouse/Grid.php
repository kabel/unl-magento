<?php

class Unl_Core_Block_Adminhtml_Warehouse_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('warehouse_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_core/warehouse')
            ->getResourceCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('warehouse_id', array(
            'header'    => Mage::helper('shipping')->__('ID'),
            'align'     =>'right',
            'width'     => '50px',
            'index'     => 'warehouse_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('shipping')->__('Warehouse Name'),
            'align'     =>'left',
            'index'     => 'name',
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('shipping')->__('Notification Email'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'email',
        ));

        $this->addColumn('action', array(
            'header'    => Mage::helper('shipping')->__('Action'),
            'width'     => '50px',
            'type'      => 'action',
            'getter'    => 'getId',
            'actions'   => array(
                array(
                    'caption' => Mage::helper('shipping')->__('Edit'),
                    'url'     => array('base'=>'*/*/edit'),
                    'field'   => 'id'
                )
            ),
            'filter'    => false,
            'sortable'  => false,
            'index'     => 'stores',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}