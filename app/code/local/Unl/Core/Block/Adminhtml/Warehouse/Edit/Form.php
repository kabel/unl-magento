<?php

class Unl_Core_Block_Adminhtml_Warehouse_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('current_warehouse');

        $form   = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $fieldset   = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('shipping')->__('Warehouse Information'),
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name'      => 'id',
                'value'     => $model->getId(),
            ));
        }

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('shipping')->__('Warehouse Name'),
            'title' => Mage::helper('shipping')->__('Warehouse Name'),
            'required' => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name' => 'email',
            'label' => Mage::helper('shipping')->__('Notification Email'),
            'title' => Mage::helper('shipping')->__('Notification Email'),
        	'required' => true,
            'class' => 'validate-email',
        ));

        $form->addValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
