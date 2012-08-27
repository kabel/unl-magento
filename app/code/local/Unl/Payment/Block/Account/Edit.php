<?php

class Unl_Payment_Block_Account_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_payment';
        $this->_controller = 'account';

        parent::__construct();
        $this->_updateButton('save', 'label', Mage::helper('unl_payment')->__('Save Account'));
        $this->_updateButton('delete', 'label', Mage::helper('unl_payment')->__('Delete Account'));
    }

    /**
     * Add child HTML to layout
     *
     * @return Unl_Payment_Block_Account_Edit
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setChild('assign_accordion', $this->getLayout()->createBlock('unl_payment/account_edit_assigned'));

        return $this;
    }

    /**
     * Retrieve Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_account')->getId()) {
            return Mage::helper('unl_payment')->__("Edit Account '%s'", $this->htmlEscape(Mage::registry('current_account')->getName()));
        }
        return Mage::helper('unl_payment')->__('New Account');
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
     * Retrieve Assigned Products Accordion HTML
     *
     * @return string
     */
    public function getAssignAccordionHtml()
    {
        return $this->getChildHtml('assign_accordion');
    }
}
