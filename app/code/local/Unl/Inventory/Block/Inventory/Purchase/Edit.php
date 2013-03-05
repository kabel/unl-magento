<?php

class Unl_Inventory_Block_Inventory_Purchase_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'unl_inventory';
        $this->_controller = 'inventory_purchase';

        parent::__construct();

        $this->_removeButton('delete');
        if (!Mage::helper('unl_inventory')->isAllowedInventoryEdit()) {
            $this->_removeButton('save');
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('unl_inventory')->__('Purchase Details');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save');
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/catalog_inventory/edit', array('id' => Mage::registry('current_product')->getId()));
    }
}
