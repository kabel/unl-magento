<?php

class Unl_Core_Model_Admin_Observer
{
    public function beforeUserSave($observer)
    {
        /* @var $user Mage_Admin_Model_User */
        $user = $observer->getEvent()->getObject();
        
        $scope = $user->getScope();
        
        if ($user->getAll() || empty($scope)) {
            $user->setData('scope', null);
        } else {
            if (is_array($scope)) {
                $scope = implode(',', $scope);
            }
            $user->setData('scope', $scope);
        }
    }
    
    public function isAllowedAddRootCategory($observer)
    {
        $options = $observer->getEvent()->getOptions();
        $user = Mage::getSingleton('admin/session')->getUser();
        
        if ($user->getScope()) {
            $options->setIsAllow(false);
        }
    }
    
    public function setStoreParamFromUser($observer)
    {
        $user  = Mage::getSingleton('admin/session')->getUser();
        $request = Mage::app()->getRequest();
        
        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            if ($store = $request->getParam('store')) {
                if (!in_array($store, $scope)) {
                    $request->setParam('store', current($scope));
                }
            } else {
                $request->setParam('store', current($scope));
            }
        }
    }
    
    public function catalogProductEditActionPreDispatch($observer)
    {
        $request = Mage::app()->getRequest();
        $user = Mage::getSingleton('admin/session')->getUser();
        
        if (($productId = (int) $request->getParam('id')) && ($scope = $user->getScope())) {
            $scope = explode(',', $scope);
            if ($productId && $product = Mage::getModel('catalog/product')->load($productId)) {
                $source = $product->getSourceStoreView();
                if ($source && !in_array($source, $scope)) {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Access to requested product denied'));
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