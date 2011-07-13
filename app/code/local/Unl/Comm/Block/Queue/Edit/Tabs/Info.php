<?php

class Unl_Comm_Block_Queue_Edit_Tabs_Info extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $queue = $this->getQueue();

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    =>  Mage::helper('unl_comm')->__('Queue Information'),
            'class'     => 'fieldset-wide'
        ));

        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        if ($queue->getQueueStatus() == Unl_Comm_Model_Queue::STATUS_NEVER) {
            $fieldset->addField('date', 'date',array(
                'name'      =>    'start_at',
                'time'      =>    true,
                'format'    =>    $outputFormat,
                'label'     =>    Mage::helper('unl_comm')->__('Queue Date Start'),
                'image'     =>    $this->getSkinUrl('images/grid-cal.gif')
            ));
        } else {
            $fieldset->addField('date','date',array(
                'name'      => 'start_at',
                'time'      => true,
                'disabled'  => 'true',
                'format'    => $outputFormat,
                'label'     => Mage::helper('unl_comm')->__('Queue Date Start'),
                'image'     => $this->getSkinUrl('images/grid-cal.gif')
            ));
        }

        if ($queue->getQueueStartAt()) {
            $form->getElement('date')->setValue(
                Mage::app()->getLocale()->date($queue->getQueueStartAt(), Varien_Date::DATETIME_INTERNAL_FORMAT)
            );
        }

        $fieldset->addField('subject', 'text', array(
            'name'      =>'subject',
            'label'     => Mage::helper('unl_comm')->__('Subject'),
            'required'  => true,
            'value'     => $queue->getMessageSubject()
        ));

        $fieldset->addField('sender_name', 'text', array(
            'name'      =>'sender_name',
            'label'     => Mage::helper('unl_comm')->__('Sender Name'),
            'title'     => Mage::helper('unl_comm')->__('Sender Name'),
            'required'  => true,
            'value'     => $queue->getMessageSenderName()
        ));

        $fieldset->addField('sender_email', 'text', array(
            'name'      =>'sender_email',
            'label'     => Mage::helper('unl_comm')->__('Sender Email'),
            'title'     => Mage::helper('unl_comm')->__('Sender Email'),
            'class'     => 'validate-email',
            'required'  => true,
            'value'     => $queue->getMessageSenderEmail()
        ));

        $fieldset->addField('type','select', array(
            'name'      =>    'type',
            'label'     =>    Mage::helper('unl_comm')->__('Message Type'),
        	'required'  =>    true,
            'value'     =>    $queue->getType() ? $queue->getType() : Mage_Core_Model_Email_Template::TYPE_HTML,
            'options'   =>    array(
                Mage_Core_Model_Email_Template::TYPE_TEXT => Mage::helper('unl_comm')->__('Text'),
                Mage_Core_Model_Email_Template::TYPE_HTML => Mage::helper('unl_comm')->__('HTML')
            )
        ));

        $widgetFilters = array('is_email_compatible' => 1);
        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
        	'widget_filters' => $widgetFilters,
        	'disable_design_css' => true,
        	'hidden' => $queue->isPlain()
        ));

        if (!$queue->isNew() && $this->getIsPreview()) {
            $fieldset->addField('text','textarea', array(
                'name'      =>    'text',
                'label'     =>    Mage::helper('unl_comm')->__('Message'),
                'value'     =>    $queue->getMessageText(),
            ));

            $fieldset->addField('styles', 'textarea', array(
                'name'          => 'styles',
                'label'         => Mage::helper('unl_comm')->__('Message Styles'),
                'value'         => $queue->getMessageStyles()
            ));

            $form->getElement('type')->setDisabled('true')->setRequired(false);
            $form->getElement('text')->setDisabled('true')->setRequired(false);
            $form->getElement('styles')->setDisabled('true')->setRequired(false);
            $form->getElement('subject')->setDisabled('true')->setRequired(false);
            $form->getElement('sender_name')->setDisabled('true')->setRequired(false);
            $form->getElement('sender_email')->setDisabled('true')->setRequired(false);
        } else {
            if ($queue->isNew()) {
                $customerIds = $queue->getCustomerIds();
                $fieldset->addField('customer', 'hidden', array(
                    'name'      => 'customer',
                    'value'     => is_array($customerIds) ? implode(',', $customerIds) : $customerIds
                ));
            }

            $fieldset->addField('text','editor', array(
                'name'      => 'text',
                'label'     => Mage::helper('unl_comm')->__('Message'),
                'state'     => 'html',
                'required'  => true,
                'value'     => $queue->getMessageText(),
                'style'     => 'width:95%; height: 600px;',
                'config'    => $wysiwygConfig
            ));

            $fieldset->addField('styles', 'textarea', array(
                'name'          => 'styles',
                'label'         => Mage::helper('unl_comm')->__('Message Styles'),
                'value'         => $queue->getMessageStyles(),
                'style'         => 'width:95%; height: 300px;',
            ));
        }

        $this->setForm($form);
        return $this;
    }

    public function getQueue()
    {
        return Mage::registry('current_queue');
    }

    /**
     * Getter for availability preview mode
     *
     * @return boolean
     */
    public function getIsPreview()
    {
        return !in_array($this->getQueue()->getQueueStatus(), array(
            Unl_Comm_Model_Queue::STATUS_NEVER,
            Unl_Comm_Model_Queue::STATUS_PAUSE
        ));
    }

    /**
    * ######################## TAB settings #################################
    */
    public function getTabLabel()
    {
        return Mage::helper('unl_comm')->__('Queue Information');
    }

    public function getTabTitle()
    {
        return Mage::helper('unl_comm')->__('Queue Information');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }
}
