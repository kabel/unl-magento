<?php

class Unl_CustomerTag_Block_Tag_Assigned_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_assigned_customers_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        if ($this->_getTagId()) {
            $this->setDefaultFilter(array('is_tagged'=>1));
        }
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id');

        $collection->joinField('is_tagged', 'unl_customertag/link', new Zend_Db_Expr('tag_id IS NOT NULL'), 'customer_id=entity_id',
            array('tag_id' => $this->_getTagId()), 'left');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('is_tagged', array(
            'header'   => Mage::helper('unl_customertag')->__('Is Tagged'),
            'index'    => 'is_tagged',
            'type'     => 'options',
            'options'  => array(
                '1' => Mage::helper('unl_customertag')->__('Yes'),
                '0' => Mage::helper('unl_customertag')->__('No'),
            ),
            'width' => '50px',
            'align' => 'center',
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));
        /*$this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));*/
        $this->addColumn('email', array(
            'header'    => Mage::helper('customer')->__('Email'),
            'width'     => '150',
            'index'     => 'email'
        ));

        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionHash();

        $this->addColumn('group', array(
            'header'    =>  Mage::helper('customer')->__('Group'),
            'width'     =>  '100',
            'index'     =>  'group_id',
            'type'      =>  'options',
            'options'   =>  $groups,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customer/edit', array(
            'tab' => 'customertag',
            'id' => $row->getId()
        ));
    }

    /**
     * Current tag getter
     *
     * @return Unl_CustomerTag_Model_Tag
     */
    public function getCurrentTag()
    {
        return Mage::registry('current_tag');
    }

    protected function _getTagId()
    {
        return $this->getCurrentTag()->getId();
    }
}
