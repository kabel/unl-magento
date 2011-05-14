<?php

class Unl_CustomerTag_Block_Tag_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_form');
        $this->setTitle(Mage::helper('tag')->__('Block Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_tag');

        $disabled = (bool)$model->getIsSystem();

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('tag')->__('General Information')));

        if ($model->getTagId()) {
            $fieldset->addField('tag_id', 'hidden', array(
                'name' => 'tag_id',
            ));
        }

        $fieldset->addField('form_key', 'hidden', array(
            'name'  => 'form_key',
            'value' => Mage::getSingleton('core/session')->getFormKey(),
        ));


        $fieldset->addField('name', 'text', array(
            'name' => 'tag_name',
            'label' => Mage::helper('tag')->__('Tag Name'),
            'title' => Mage::helper('tag')->__('Tag Name'),
            'required' => true,
            'disabled' => $disabled
        ));

        if ( Mage::getSingleton('adminhtml/session')->getCustomerTagData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getCustomerTagData(true));
        } else {
            $form->addValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
