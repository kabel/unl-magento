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
}
