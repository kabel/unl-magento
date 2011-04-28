<?php

class Unl_Core_Block_Adminhtml_Warehouse extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_warehouse';
        $this->_headerText = Mage::helper('shipping')->__('Warehouses');
        $this->_addButtonLabel = Mage::helper('shipping')->__('Add New Warehouse');
        parent::__construct();
    }
}
