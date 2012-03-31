<?php

class Unl_CustomerTag_Block_Category_Tab_Access extends Mage_Adminhtml_Block_Widget_Grid
{
	/**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('category_access_tag_grid');
        $this->setDefaultSort('name');
        $this->setUseAjax(true);
        if ($this->_getCategory()->getId()) {
            $this->setDefaultFilter(array('in_tags'=>1));
        }
    }

    /**
     * Retirve currently edited category model
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _getCategory()
    {
        return Mage::registry('current_category');
    }

    /**
     * Add filter
     *
     * @param object $column
     * @return Unl_CustomerTag_Block_Category_Tab_Access
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in tag flag
        if ($column->getId() == 'in_tags') {
            $tagIds = $this->_getSelectedTags();
            if (empty($tagIds)) {
                $tagIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('tag_id', array('in'=>$tagIds));
            } else {
                if($tagIds) {
                    $this->getCollection()->addFieldToFilter('tag_id', array('nin'=>$tagIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_customertag/tag')->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_tags', array(
            'header_css_class'  => 'a-center',
            'type'              => 'checkbox',
            'name'              => 'in_tags',
            'values'            => $this->_getSelectedTags(),
            'align'             => 'center',
            'index'             => 'tag_id'
        ));

        $this->addColumn('tag_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'tag_id'
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'index'     => 'name'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/customerTag_category/grid', array('_current'=>true));
    }

    /**
     * Retrieve selected customer tags for access
     *
     * @return array
     */
    protected function _getSelectedTags()
    {
        $tags = $this->getRequest()->getPost('category_access', null);
        if (!is_array($tags)) {
            $tags = $this->getSelectedTags();
        }
        return $tags;
    }

    /**
     * Appends a grid serializer for this grid to a specified block
     *
     * @param Mage_Core_Block_Abstract $block [OPTIONAL] The block to append to, defaults to the parent block
     * @return Unl_CustomerTag_Block_Category_Tab_Access
     */
    public function appendSerializer($block = null)
    {
        if (null === $block) {
            $block = $this->getParentBlock();
        }

        if ($block) {
            $serializer = $this->getLayout()->createBlock('adminhtml/widget_grid_serializer');
            $block->append($serializer);
            $serializer->initSerializerBlock($this, 'getSelectedTags',
                    Mage::helper('unl_customertag')->getAccessStorageName(), 'category_access');
        }

        return $this;
    }

    /**
     * Retrieve category access tags
     *
     * @return array
     */
    public function getSelectedTags()
    {
        return Mage::getResourceModel('unl_customertag/tag')->getCategoryAccess($this->_getCategory());
    }
}
