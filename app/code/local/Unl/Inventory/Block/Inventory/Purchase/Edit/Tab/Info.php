<?php

class Unl_Inventory_Block_Inventory_Purchase_Edit_Tab_Info extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('purchase_');
        $form->setFieldNameSuffix('purchase');

        $purchase = Mage::registry('current_purchase');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('unl_inventory')->__('Update Purchase Amount'))
        );

        $fieldset->addField('created_at', 'note', array(
            'label'    => Mage::helper('unl_inventory')->__('Created At'),
            'text'     => Mage::helper('core')->formatDate($purchase->getCreatedAt(), 'medium', true),
        ));

        $fieldset->addField('qty_remaining', 'note', array(
            'label'    => Mage::helper('unl_inventory')->__('Qty Remaining'),
            'text'     => $purchase->getQtyOnHand() * 1,
        ));

        $fieldset->addField('qty', 'note', array(
            'label'    => Mage::helper('unl_inventory')->__('Qty'),
            'text'     => $purchase->getQty() * 1,
        ));

        $fieldset->addField('amount', 'text', array(
            'name'     => 'amount',
            'label'    => Mage::helper('unl_inventory')->__('Cost'),
            'required' => true,
            'class'    => 'validate-zero-or-greater',
            'note'     => Mage::helper('unl_inventory')->__('Please enter the full amount of the purchase.'),
        ));

        $fieldset->addField('note', 'textarea', array(
            'name'     => 'note',
            'label'    => Mage::helper('unl_inventory')->__('Update Note'),
        ));

        $form->setValues($purchase->getData());
        $this->setForm($form);
        return $this;
    }
}
