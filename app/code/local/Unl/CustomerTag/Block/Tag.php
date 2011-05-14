<?php

class Unl_CustomerTag_Block_Tag extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	/**
     * Modify header & button labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_customertag';
        $this->_controller = 'tag';
        $this->_headerText = Mage::helper('unl_customertag')->__('Customer Tags');
        $this->_addButtonLabel = Mage::helper('unl_customertag')->__('Add New Customer Tag');
        parent::__construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass() {
        return 'icon-head head-customer-tag';
    }
}
