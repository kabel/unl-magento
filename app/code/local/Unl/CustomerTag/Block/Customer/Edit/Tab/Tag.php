<?php

class Unl_CustomerTag_Block_Customer_Edit_Tab_Tag extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('customertag_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        if( $this->getCustomerId() instanceof Mage_Customer_Model_Customer ) {
            $this->setCustomerId( $this->getCustomerId()->getId() );
        }

        $collection = Mage::getResourceModel('unl_customertag/tag_collection')
            ->addCustomerFilter($this->getCustomerId());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('unl_customertag')->__('Tag Name'),
            'index'     => 'name',
        ));

        $this->addColumn('created_at', array(
            'header'    =>  Mage::helper('unl_customertag')->__('Tagged On'),
            'type'      =>  'datetime',
            'index'     => 'created_at',
            'width'     => '150',
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customerTag/edit', array(
            'id' => $row->getTagId()
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/customerTag_customer/grid', array(
            '_current' => true,
            'id'       => $this->getCustomerId()
        ));
    }
}
