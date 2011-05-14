<?php

class Unl_CustomerTag_Block_Tag_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Add and update buttons
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_customertag';
        $this->_controller = 'tag';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('unl_customertag')->__('Save Tag'));
        $this->addButton('save_and_edit_button', array(
            'label'   => Mage::helper('unl_customertag')->__('Save and Continue Edit'),
            'onclick' => "saveAndContinueEdit('" . $this->getSaveAndContinueUrl() . "')",
            'class'   => 'save'
        ), 1);

        if ($this->getTagIsReadOnly()) {
            $this->_removeButton('delete');
        } else {
            $this->_updateButton('delete', 'label', Mage::helper('unl_customertag')->__('Delete Tag'));
        }
    }

    /**
     * Add child HTML to layout
     *
     * @return Mage_Adminhtml_Block_Tag_Edit
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('tag_assign_accordion', $this->getLayout()->createBlock('unl_customertag/tag_edit_assigned'));
        return $this;
    }

    /**
     * Retrieve Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_tag')->getId()) {
            return Mage::helper('tag')->__("Edit Tag '%s'", $this->htmlEscape(Mage::registry('current_tag')->getName()));
        }
        return Mage::helper('tag')->__('New Tag');
    }

    public function getTagIsReadOnly()
    {
        return (bool)Mage::registry('current_tag')->getIsSystem();
    }

    /**
     * Retrieve Assigned Tags Accordion HTML
     *
     * @return string
     */
    public function getTagAssignAccordionHtml()
    {
        return $this->getChildHtml('tag_assign_accordion');
    }

    /**
     * Retrieve Tag Save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    /**
     * Retrieve Tag SaveAndContinue URL
     *
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array('_current' => true, 'continue' => '1'));
    }
}
