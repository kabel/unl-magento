<?php

class Unl_Spam_Block_Adminhtml_Blacklist_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('blacklist_form');
        $this->setTitle(Mage::helper('unl_spam')->__('Basic Information'));
    }

    protected function _prepareForm()
    {
        $model = Mage::registry('current_blacklist');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post',
            'use_container' => true,
        ));

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('unl_spam')->__('Basic Information')));

        $fieldset->addField('remote_addr', 'text', array(
            'name' => 'remote_addr',
            'label' => Mage::helper('unl_spam')->__('IP Address'),
            'title' => Mage::helper('unl_spam')->__('IP Address'),
            'required' => true,
        ));

        $fieldset->addField('cidr_bits', 'text', array(
            'name' => 'cidr_bits',
            'label' => Mage::helper('unl_spam')->__('CIDR Mask Bits'),
            'title' => Mage::helper('unl_spam')->__('CIDR Mask Bits'),
            'class' => 'validate-digits'
        ));

        $values = Mage::getSingleton('unl_spam/source_responsetype')->toOptionArray();
        $fieldset->addField('response_type', 'select', array(
            'name' => 'response_type',
            'label' => Mage::helper('unl_spam')->__('Response Type'),
            'title' => Mage::helper('unl_spam')->__('Response Type'),
            'required' => true,
            'values' => $values
        ));

        if ($model->getId()) {
            $fieldset->addField('created_at', 'label', array(
                'name' => 'created_at',
                'label' => Mage::helper('unl_spam')->__('Created At'),
                'value_filter' => Mage::getSingleton('unl_spam/filter_datetime'),
            ));

            $fieldset->addField('last_seen', 'label', array(
                'name' => 'last_seen',
                'label' => Mage::helper('unl_spam')->__('Last Seen'),
                'value_filter' => Mage::getSingleton('unl_spam/filter_datetime'),
            ));

            $fieldset->addField('strikes', 'label', array(
                'name' => 'strikes',
                'label' => Mage::helper('unl_spam')->__('Strikes'),
            ));
        }

        $fieldset->addField('comment', 'textarea', array(
            'name' => 'comment',
            'label' => Mage::helper('unl_spam')->__('Comment'),
            'title' => Mage::helper('unl_spam')->__('Comment'),
        ));

        if ( Mage::getSingleton('adminhtml/session')->getBlacklistData() ) {
            $form->addValues(Mage::getSingleton('adminhtml/session')->getBlacklistData(true));
        } else {
            $form->addValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
