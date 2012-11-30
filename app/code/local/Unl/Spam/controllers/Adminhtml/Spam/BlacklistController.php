<?php

class Unl_Spam_Adminhtml_Spam_BlacklistController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_blacklist');

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $this->_redirect('*/*/');
    }

    public function massRespUpdateAction()
    {
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->$this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_blacklist');
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_blacklist');
        $this->renderLayout();
    }

    public function saveAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_blacklist');
        $this->renderLayout();
    }

    public function deleteAction()
    {
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/spam_blacklist');
    }
}
