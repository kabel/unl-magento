<?php

class Unl_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fetchServerFile($path)
    {
        return file_get_contents('http://' . Mage::helper('core/http')->getHttpHost() . $path);
    }
    
    public function isCustomerAllowed($category, $reload = true)
    {
        $_cat = $category;
        if (!($category instanceof Mage_Catalog_Model_Category)) {
            $_cat = Mage::getModel('catalog/category')->load($category->getId());
        } else if ($reload) {
            $_cat->load($_cat->getId());
        }
        
        if ($acl = $_cat->getGroupAcl()) {
            $session = Mage::getSingleton('customer/session');
            if (!empty($acl) && !$session->isLoggedIn()) {
                return false;
            }
            
            $customer = $session->getCustomer();
            if (!in_array($customer->getGroupId(), $acl)) {
                return false;
            }
        }
        
        return true;
    }
}