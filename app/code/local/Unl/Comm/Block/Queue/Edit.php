<?php

class Unl_Comm_Block_Queue_Edit extends Mage_Adminhtml_Block_Template
{
    /**
     * Retrieve current Newsletter Queue Object
     *
     * @return Mage_Newsletter_Model_Queue
     */
    public function getQueue()
    {
        return Mage::registry('current_queue');
    }

    public function getCustomerIds()
    {
        return $this->getQueue()->getCustomerIds();
    }

    protected function _beforeToHtml() {

        $this->setTemplate('unl/comm/queue/edit.phtml');

        $this->setChild('form',
            $this->getLayout()->createBlock('unl_comm/queue_edit_form','form')
        );

        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        // Load Wysiwyg on demand and Prepare layout
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('unl_comm')->__('Save Message'),
                    'onclick'   => 'queueControl.save()',
                    'class'     => 'save'
                ))
        );

        $this->setChild('save_and_resume',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('unl_comm')->__('Save and Resume'),
                    'onclick'   => 'queueControl.resume()',
                    'class'     => 'save'
                ))
        );

        $this->setChild('preview_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('unl_comm')->__('Preview'),
                    'onclick'   => 'queueControl.preview()'
                ))
        );

        if (!$this->getQueue()->isNew()) {
            $this->setChild('reset_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('unl_comm')->__('Reset'),
                        'onclick'   => 'window.location = window.location'
                    ))
            );
        }

        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('unl_comm')->__('Back'),
                        'onclick' => "window.location.href = '" . $this->getUrl((
                            $this->getCustomerIds() ? 'adminhtml/customer/' : '*/*')) . "'",
                        'class'   => 'back'
                    )
                )
        );

        if ($this->getCanDelete() && $this->getDeleteUrl()) {
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                	->setData(array(
                	    'label'   => Mage::helper('unl_comm')->__('Delete'),
                	    'onclick' => sprintf("confirmSetLocation('%s', '%s')", Mage::helper('unl_comm')->__('Are you sure?'), $this->getDeleteUrl()),
                	    'class'   => 'delete'
                	))
            );
        }

        return parent::_prepareLayout();
    }

    public function getDeleteUrl()
    {
        if ($this->getQueue()->getId()) {
            $params = array('id' => $this->getQueue()->getId());
        } else {
            return false;
        }
        return $this->getUrl('*/*/delete', $params);
    }

    public function getSaveUrl()
    {
        if ($this->getQueue()->getId()) {
            $params = array('id' => $this->getQueue()->getId());
        } else {
            $params = array();
        }
        return $this->getUrl('*/*/save', $params);
    }

    /**
     * Return preview action url for form
     *
     * @return string
     */
    public function getPreviewUrl()
    {
        return $this->getUrl('*/*/preview');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Resume Button HTML
     *
     * @return string
     */
    public function getResumeButtonHtml()
    {
        return $this->getChildHtml('save_and_resume');
    }

    /**
     * Retrieve Preview Button HTML
     *
     * @return string
     */
    public function getPreviewButtonHtml()
    {
        return $this->getChildHtml('preview_button');
    }

    /**
     * Retrieve Delete Button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getCanDelete()
    {
        return in_array($this->getQueue()->getQueueStatus(), array(
            Unl_Comm_Model_Queue::STATUS_NEVER
        ));
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
     * Getter for availability resume action
     *
     * @return boolean
     */
    public function getCanResume()
    {
        return in_array($this->getQueue()->getQueueStatus(), array(
            Unl_Comm_Model_Queue::STATUS_PAUSE
        ));
    }

    /**
     * Getter for header text
     *
     * @return boolean
     */
    public function getHeaderText()
    {
        return ( $this->getIsPreview() ? Mage::helper('unl_comm')->__('View Message') : Mage::helper('unl_comm')->__('Edit Message'));
    }
}