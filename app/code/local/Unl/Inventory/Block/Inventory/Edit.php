<?php

class Unl_Inventory_Block_Inventory_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'unl_inventory';
        $this->_controller = 'inventory';

        parent::__construct();

        $this->_removeButton('delete');
        if (Mage::helper('unl_inventory')->isAllowedInventoryEdit()
            && Mage::helper('unl_inventory')->getIsAuditInventory(Mage::registry('current_product'))) {
            $this->_addButton('save_and_continue', array(
                'label'     => Mage::helper('unl_inventory')->__('Save and Continue Edit'),
                'onclick'   => 'saveAndContinueEdit(\''.$this->_getSaveAndContinueUrl().'\')',
                'class' => 'save'
            ), 10);
        } else {
            $this->_removeButton('save');
        }
    }

    public function getHeaderText()
    {
        return Mage::helper('unl_inventory')->__('Product Inventory');
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
            'tab'       => '{{tab_id}}'
        ));
    }
}
