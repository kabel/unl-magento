<?php
class Unl_Core_Block_Adminhtml_System_Email_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('adminhtml')->__('Template Information'))
        );

        $fieldset->addField('template_code', 'text', array(
            'name'=>'template_code',
            'label' => Mage::helper('adminhtml')->__('Template Name'),
            'required' => true

        ));

        $fieldset->addField('template_subject', 'text', array(
            'name'=>'template_subject',
            'label' => Mage::helper('adminhtml')->__('Template Subject'),
            'required' => true
        ));

        $fieldset->addField('template_text', 'editor', array(
            'name'=>'template_text',
            'wysiwyg' => !Mage::registry('email_template')->isPlain(),
            'label' => Mage::helper('adminhtml')->__('Template Content'),
            'required' => true,
            'theme' => 'advanced',
            'disableCss' => true,
            //'state' => 'html',
            //'style' => 'width:98%; height: 600px;',
        ));

        if (Mage::registry('email_template')->getId()) {
            $form->addValues(Mage::registry('email_template')->getData());
        }

        if ($values = Mage::getSingleton('adminhtml/session')->getData('email_template_form_data', true)) {
            $form->setValues($values);
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
