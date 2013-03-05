<?php

class Unl_Inventory_Block_Inventory_Purchase_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $purchase = Mage::registry('current_purchase');
        $form->addField('purchase_id', 'hidden', array(
            'name' => 'id',
            'value' => $purchase->getId()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
