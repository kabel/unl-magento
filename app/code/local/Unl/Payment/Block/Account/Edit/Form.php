<?php

class Unl_Payment_Block_Account_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('account_form');
        $this->setTitle(Mage::helper('unl_payment')->__('Account Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_account');

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('unl_payment')->__('General Information')));

        $fieldset->addField('form_key', 'hidden', array(
            'name'  => 'form_key',
            'value' => Mage::getSingleton('core/session')->getFormKey(),
        ));

        $fieldset->addField('group_id', 'select', array(
            'name' => 'group_id',
            'label' => Mage::helper('unl_payment')->__('Merchant'),
            'title' => Mage::helper('unl_payment')->__('Merchant'),
            'required' => true,
            'options' => Mage::getModel('unl_core/store_source_filter_group')->getAllOptions(),
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('unl_payment')->__('Account Name'),
            'title' => Mage::helper('unl_payment')->__('Account Name'),
            'required' => true,
        ));

        if (Mage::getSingleton('adminhtml/session')->getPaymentAccountData()) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getPaymentAccountData(true));
        } else {
            $form->addValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
