<?php

class Unl_TestModule_Block_Test extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('tester/index.phtml');
    }
    
    protected function shout()
    {
        $temp = Mage::getModel('catalog/entity_attribute');
        $temp->getResource()->loadByCode($temp, 10, 'source_store_view');
        
        return "Hello World!";
    }
}
