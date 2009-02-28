<?php

class Unl_Core_Block_Adminhtml_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function getStoreSwitcherHtml()
    {
        $session  = Mage::getSingleton('admin/session');
        /* @var $session Mage_Admin_Model_Session */
        $request = Mage::app()->getRequest();
        $user = $session->getUser();
        if (!is_null($user->getStore())) {
            return '';
        } else {
            return parent::getStoreSwitcherHtml();
        }
    }
}