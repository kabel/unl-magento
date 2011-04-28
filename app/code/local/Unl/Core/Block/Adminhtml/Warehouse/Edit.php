<?php

class Unl_Core_Block_Adminhtml_Warehouse_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'unl_core';
        $this->_controller = 'adminhtml_warehouse';

        parent::__construct();
    }

    public function getHeaderText()
    {
        $model = Mage::registry('current_warehouse');
        if ($model->getId()) {
            return Mage::helper('shipping')->__("Edit Warehouse '%s'", $this->htmlEscape($model->getName()));
        }
        else {
            return Mage::helper('shipping')->__('New Warehouse');
        }
    }
}
