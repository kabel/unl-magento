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
        $this->setDefaultFilter(array('in_tag'=>1));
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_tag') {
            $tagIds = $this->_getSelectedTags();
            if (empty($tagIds)) {
                $tagIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.tag_id', array('in'=>$tagIds));
            } else {
                if($tagIds) {
                    $this->getCollection()->addFieldToFilter('main_table.tag_id', array('nin'=>$tagIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('unl_customertag/tag_collection');

        $collection->addCustomerFilter($this->getCustomerId(), true);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('in_tag', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'field_name'        => 'in_tag',
            'values'            => $this->_getSelectedTags(),
            'align'             => 'center',
            'index'             => 'tag_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('unl_customertag')->__('Tag Name'),
            'index'     => 'name',
        ));

        $this->addColumn('created_at', array(
            'header'    =>  Mage::helper('unl_customertag')->__('Tagged On'),
            'type'      =>  'datetime',
            'index'     => 'created_at',
            'width'     => '200px',
        ));

        return parent::_prepareColumns();
    }

    protected function _getSelectedTags()
    {
        $tags = $this->getRequest()->getPost('assigned_tags', null);
        if (!is_array($tags)) {
            $tags = $this->getLinkedTags();
        }
        return $tags;
    }

    public function getLinkedTags()
    {
        /* @var $resource Unl_CustomerTag_Model_Resource_Tag */
        $resource = Mage::getResourceModel('unl_customertag/tag');
        return $resource->getCustomerTags(Mage::registry('current_customer'));
    }

    public function getCustomerId()
    {
        return Mage::registry('current_customer')->getId();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/customerTag/edit', array(
            'id' => $row->getTagId()
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/customerTag_customer/gridOnly', array(
            '_current' => true,
            'id'       => $this->getCustomerId()
        ));
    }
}
