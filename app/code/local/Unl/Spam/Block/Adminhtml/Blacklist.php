<?php

class Unl_Spam_Block_Adminhtml_Blacklist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	/**
     * Modify header & button labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_spam';
        $this->_controller = 'adminhtml_blacklist';
        $this->_headerText = Mage::helper('unl_spam')->__('SPAM Blacklist');
        parent::__construct();

        //$this->_removeButton('add');
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass() {
        return 'icon-head head-spam-list';
    }
}
