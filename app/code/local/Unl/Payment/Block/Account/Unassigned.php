<?php

class Unl_Payment_Block_Account_Unassigned extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Modify header & button labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_payment';
        $this->_controller = 'account_unassigned';
        $this->_headerText = Mage::helper('unl_payment')->__('Products without Payment Account');
        parent::__construct();

        $this->setBackUrl($this->getUrl('*/*/'));
        $this->_addBackButton();

        $this->removeButton('add');
    }
}
