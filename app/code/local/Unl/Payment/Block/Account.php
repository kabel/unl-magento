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

        $this->_addButton('unassigned', array(
            'label'     => Mage::helper('unl_payment')->__('Show Unassigned Products'),
            'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/unassigned') .'\')',
        ));
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
