<?php

class Unl_Inventory_Block_Products extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'unl_inventory';
        $this->_controller = 'products';
        $this->_headerText = Mage::helper('unl_inventory')->__('Manage Product Inventory');
        parent::__construct();

        $this->_removeButton('add');
    }
}
