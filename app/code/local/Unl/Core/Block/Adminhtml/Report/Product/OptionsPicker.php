<?php

class Unl_Core_Block_Adminhtml_Report_Product_OptionsPicker extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_report_product_optionsPicker';
        $this->_headerText = Mage::helper('reports')->__('Ordered Products with Options');
        parent::__construct();
        $this->_removeButton('add');
    }
}
