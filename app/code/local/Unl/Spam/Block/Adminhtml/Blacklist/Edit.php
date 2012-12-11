<?php

class Unl_Spam_Block_Adminhtml_Blacklist_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Prep form info
     *
     * @return void
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_spam';
        $this->_controller = 'adminhtml_blacklist';

        parent::__construct();
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_blacklist')->getId()) {
            return Mage::helper('unl_spam')->__('Edit Blacklisting');
        }
        return Mage::helper('unl_spam')->__('New Blacklisting');
    }

    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
}
