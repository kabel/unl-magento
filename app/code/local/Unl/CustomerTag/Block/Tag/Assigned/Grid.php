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
            $this->setDefaultFilter(array('in_customers'=>1));
        }
    }

	/**
     * Add filter to grid columns
     *
     * @param mixed $column
     * @return Mage_Adminhtml_Block_Tag_Assigned_Grid
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in customers flag
        if ($column->getId() == 'in_customers') {
            $customerIds = $this->_getSelectedCustomers();
            if (empty($customerIds)) {
                $customerIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in'=>$customerIds));
            } else {
                if($customerIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin'=>$customerIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_customers', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'field_name'        => 'in_customers',
            'values'            => $this->_getSelectedCustomers(),
            'align'             => 'center',
            'index'             => 'entity_id'
        ));

        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('customer')->__('ID'),
            'width'     => '50px',
            'index'     => 'entity_id',
            'type'  => 'number',
        ));
        /*$this->addColumn('firstname', array(
            'header'    => Mage::helper('customer')->__('First Name'),
            'index'     => 'firstname'
        ));
        $this->addColumn('lastname', array(
            'header'    => Mage::helper('customer')->__('Last Name'),
            'index'     => 'lastname'
        ));*/
        $this->addColumn('name', array(
            'header'    => Mage::helper('customer')->__('Name'),
            'index'     => 'name'
        ));
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

	/**
     * Retrieve related customers
     *
     * @return array
     */
    protected function _getSelectedCustomers()
    {
        $customers = $this->getRequest()->getPost('assigned_customers', null);
        if (!is_array($customers)) {
            $customers = $this->getLinkedCustomers();
        }
        return $customers;
    }

    /**
     * Retrieve saved related customers
     *
     * @return array
     */
    public function getLinkedCustomers()
    {
        return $this->getCurrentTag()->getLinkedCustomerIds();
    }

	/**
     * Retrieve Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/assignedGridOnly', array('_current' => true));
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
