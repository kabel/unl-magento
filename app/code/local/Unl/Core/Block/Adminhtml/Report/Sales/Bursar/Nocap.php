<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_sales_bursar_nocap';
        $this->_headerText = Mage::helper('reports')->__('Bursar Report: Non-Captured');
        parent::__construct();
        $this->_removeButton('add');
    }

}