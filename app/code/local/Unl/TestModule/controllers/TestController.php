<?php
class Unl_TestModule_TestController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        //$this->_setActiveMenu('dashboard');

        $this->_addContent($this->getLayout()->createBlock('unl_tester/test'));
        
        $this->renderLayout();
    }
}