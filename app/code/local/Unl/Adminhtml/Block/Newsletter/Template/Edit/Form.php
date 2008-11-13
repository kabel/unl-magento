<?php

class Unl_Adminhtml_Block_Newsletter_Template_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
     /**
     * Constructor
     *
     * Initialize form
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Prepare form for render
     */
    public function renderPrepare($template)
    {
        $form = new Varien_Data_Form();

        if($this->getRequest()->isPost()) {
            $post = $this->getRequest()->getPost();
            if (isset($post['template_id'])) {
                unset($post['template_id']);
            }

            if (isset($post['template_type'])) {
                unset($post['template_type']);
            }

            $template->addData($post);
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('newsletter')->__('Template Information'), 'class' => 'fieldset-wide'));

        $fieldset->addField('code', 'text', array(
            'name'=>'code',
            'label' => Mage::helper('newsletter')->__('Template Name'),
            'title' => Mage::helper('newsletter')->__('Template Name'),
            'class' => 'required-entry',
            'required' => true,
             'value' => $template->getTemplateCode()
        ));

        $fieldset->addField('subject', 'text', array(
            'name'=>'subject',
            'label' => Mage::helper('newsletter')->__('Template Subject'),
            'title' => Mage::helper('newsletter')->__('Template Subject'),
            'class' => 'required-entry',
            'required' => true,
            'value' => $template->getTemplateSubject()
        ));

        $fieldset->addField('sender_name', 'text', array(
            'name'=>'sender_name',
            'label' => Mage::helper('newsletter')->__('Sender Name'),
            'title' => Mage::helper('newsletter')->__('Sender Name'),
            'class' => 'required-entry',
            'required' => true,
            'value' => $template->getTemplateSenderName()
        ));

        $fieldset->addField('sender_email', 'text', array(
            'name'=>'sender_email',
            'label' => Mage::helper('newsletter')->__('Sender Email'),
            'title' => Mage::helper('newsletter')->__('Sender Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
            'value' => $template->getTemplateSenderEmail()
        ));

        $txtType = constant(Mage::getConfig()->getModelClassName('newsletter/template') . '::TYPE_TEXT');

        $fieldset->addField('text', 'editor', array(
            'name'=>'text',
            'wysiwyg' => ($template->getTemplateType() != $txtType),
            'label' => Mage::helper('newsletter')->__('Template Content'),
            'title' => Mage::helper('newsletter')->__('Template Content'),
            'theme' => 'advanced',
            'class'	=> 'required-entry',
            'required' => true,
            'value' => $template->getTemplateText(),
        ));

        if ($template->getId()) {
            // If edit add id
            $form->addField('id', 'hidden',
                array(
                    'name'  => 'id',
                    'value' => $template->getId()
                )
            );
        }

        if($values = Mage::getSingleton('adminhtml/session')->getData('newsletter_template_form_data', true)) {
        	$form->setValues($values);
        }

        $this->setForm($form);

        return $this;
    }
}
