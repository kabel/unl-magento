<?php

class Unl_CustomerTag_Block_Tag_Edit_Assigned extends Mage_Adminhtml_Block_Widget_Accordion
{
	/**
     * Add Assigned customers accordion to layout
     *
     */
    protected function _prepareLayout()
    {
        if (is_null(Mage::registry('current_tag')->getId())) {
            return $this;
        }

        $tagModel = Mage::registry('current_tag');

        $this->setId('tag_assigned_grid');

        $this->addItem('tag_assign', array(
            'title'         => Mage::helper('unl_customertag')->__('Tagged Customers'),
            'ajax'          => true,
            'content_url'   => $this->getUrl('*/*/assigned', array('id'=>$tagModel->getId())),
        ));
        return parent::_prepareLayout();
    }
}
