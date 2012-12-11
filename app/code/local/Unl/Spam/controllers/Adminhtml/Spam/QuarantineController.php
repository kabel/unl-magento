<?php

class Unl_Spam_Adminhtml_Spam_QuarantineController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Quarantine'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_quarantine');

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massexpireAction()
    {
        $this->_getSession()->addNotice(Mage::helper('adminhtml')->__('This action has not been implemented yet.'));
        $this->_redirect('*/*/');
    }

    public function massblacklistAction()
    {
        $this->_getSession()->addNotice(Mage::helper('adminhtml')->__('This action has not been implemented yet.'));
        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/spam_quarantine');
    }
}
