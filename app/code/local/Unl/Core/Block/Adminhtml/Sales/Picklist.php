<?php

class Unl_Core_Block_Adminhtml_Sales_Picklist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_sales_picklist';
        $this->_headerText = Mage::helper('sales')->__('Pick Lists');
        parent::__construct();
        $this->_removeButton('add');
    }
}
