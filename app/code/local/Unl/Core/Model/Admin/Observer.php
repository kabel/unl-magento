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
    
    public function controllerActionPredispatch($observer)
    {
        $storeIdActions = array(
            'adminhtml_catalog_category_edit',
            'adminhtml_catalog_product_categories',
            'adminhtml_dashboard_index',
            'adminhtml_dashboard_productsViewed',
            'adminhtml_dashboard_customersNeweset',
            'adminhtml_dashboard_customersMost',
            'adminhtml_dashboard_ajaxBlock',
            'adminhtml_dashboard_tunnel',
            'adminhtml_report_customer_accounts',
            'adminhtml_report_customer_totals',
            'adminhtml_report_customer_orders',
            'adminhtml_report_product_ordered',
            'adminhtml_report_product_sold',
            'adminhtml_report_product_viewed',
            'adminhtml_report_product_lowstock',
            'adminhtml_report_product_downloads',
            'unl_core_sales_picklist_index',
            'unl_core_report_product_orderdetails'
        );
        $storeIdsActions = array(
            'adminhtml_report_sales_sales',
            'adminhtml_report_sales_coupons'
        );
        
        $controller = $observer->getEvent()->getControllerAction();
        if (in_array($controller->getFullActionName(), $storeIdActions)) {
            $this->_setStoreParamFromUser('store');
            return;
        } elseif (in_array($controller->getFullActionName(), $storeIdsActions)) {
            $this->_setStoreParamFromUser('store_ids');
            return;
        }
    }
    
    protected function _setStoreParamFromUser($param) {
        $user  = Mage::getSingleton('admin/session')->getUser();
        $request = Mage::app()->getRequest();
        
        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            if ($store = $request->getParam($param)) {
                if (!in_array($store, $scope)) {
                    $request->setParam($param, current($scope));
                }
            } else {
                $request->setParam($param, current($scope));
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