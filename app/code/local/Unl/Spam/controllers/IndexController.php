<?php

class Unl_Spam_IndexController extends Mage_Core_Controller_Front_Action
{
    public function preDispatch()
    {
        $this->setFlag('', self::FLAG_NO_START_SESSION, true);
        parent::preDispatch();
    }

    protected function _sendDenied()
    {
        $this->getResponse()->clearHeaders();

        $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
        $this->getResponse()->setHeader('Status','403 Forbidden');

        return $this;
    }

    public function deniedAction()
    {
        $this->_sendDenied();

        $this->loadLayout(array('default', 'spamBlock'));
        $this->renderLayout();
    }

    public function deniedSparseAction()
    {
        $this->_sendDenied();

        $this->getResponse()->setBody('Your IP is blocked from this service. You have been warned.');
        $this->getResponse()->setHeader('Content-Type', 'text/plain', true);
    }

    public function deniedEmptyAction()
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->setHeader('HTTP/1.1','404 Banned');
        $this->getResponse()->setHeader('Status','404 Banned');
    }

    public function unavailableAction()
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->setHeader('HTTP/1.1','503 Service Unavailable');
        $this->getResponse()->setHeader('Status','503 Service Unavailable');
    }
}
