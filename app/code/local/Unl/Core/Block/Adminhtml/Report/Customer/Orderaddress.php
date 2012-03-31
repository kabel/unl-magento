<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_customer_orderaddress';
        $this->_headerText = Mage::helper('sales')->__('Order Address');
        parent::__construct();
        $this->_removeButton('add');
    }

    public function getHeaderCssClass() {
        return 'icon-head head-sales-order-address';
    }
}
