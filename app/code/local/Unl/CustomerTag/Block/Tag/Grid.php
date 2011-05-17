<?php

class Unl_CustomerTag_Block_Tag_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customerTagGrid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_customertag/tag')->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('tag_id', array(
            'header' => Mage::helper('unl_customertag')->__('ID'),
            'width' => '50px',
            'align' => 'right',
            'index' => 'tag_id',
        ));

        $this->addColumn('name', array(
            'header' => Mage::helper('unl_customertag')->__('Name'),
            'index' => 'name',
            'width' => '200px'
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
