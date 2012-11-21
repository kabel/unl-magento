<?php

class Unl_Spam_IndexController extends Mage_Core_Controller_Front_Action
{
    public function deniedAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
        $this->getResponse()->setHeader('Status','403 Forbidden');

        $this->loadLayout(array('default', 'spamBlock'));
        $this->renderLayout();
    }

    public function unavailableAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','503 Service Unavailable');
        $this->getResponse()->setHeader('Status','503 Service Unavailable');
    }
}
