<?php

class Unl_Payment_Block_Account extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Modify header & button labels
     *
     */
    public function __construct()
    {
        $this->_blockGroup = 'unl_payment';
        $this->_controller = 'account';
        $this->_headerText = Mage::helper('unl_payment')->__('Payment Accounts');
        $this->_addButtonLabel = Mage::helper('unl_payment')->__('Add Payment Account');
        parent::__construct();
    }

    /**
     * Redefine header css class
     *
     * @return string
     */
    public function getHeaderCssClass() {
        return 'icon-head head-payment-account';
    }
}
