<?php

class Unl_Inventory_Block_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post'));

        $product = Mage::registry('current_product');
        $form->addField('product_id', 'hidden', array(
            'name' => 'product_id',
            'value' => $product->getId()
        ));

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
