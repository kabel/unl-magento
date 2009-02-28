<?php


class Unl_Core_Model_Admin_Observer
{
    public function setStoreParamFromUser($event)
    {
        $session  = Mage::getSingleton('admin/session');
        /* @var $session Mage_Admin_Model_Session */
        $request = Mage::app()->getRequest();
        $user = $session->getUser();
        
        if (!is_null($user->getStore())) {
            $request->setParam('store', $user->getStore());
        }
    }
    
    public function catalogProductEditActionPreDispatch($event)
    {
        $session  = Mage::getSingleton('admin/session');
        /* @var $session Mage_Admin_Model_Session */
        $request = Mage::app()->getRequest();
        $user = $session->getUser();
        
        if (!is_null($user->getStore())) {
            $productId  = (int) $request->getParam('id');
            
            if ($productId) {
                $product = Mage::getModel('catalog/product')->load($productId);
                $source = $product->getSourceStoreView();
                if ($source && $source != $user->getStore()) {
                    $request->setParam('forwarded', true)
                        ->setRouteName('adminhtml')
                        ->setControllerName('catalog_product')
                        ->setActionName('index')
                        ->setDispatched(false);
                }
            }
        }
        
    }
}