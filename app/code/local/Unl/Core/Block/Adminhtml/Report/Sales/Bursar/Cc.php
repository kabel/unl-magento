<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_sales_bursar_cc';
        $this->_headerText = Mage::helper('reports')->__('Bursar Report: Credit Card');
        parent::__construct();
        $this->_removeButton('add');
    }

}