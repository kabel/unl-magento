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
    
    public function beforeCmsPageSave($observer)
    {
        /* @var $page Mage_Cms_Model_Page */
        $page = $observer->getEvent()->getObject();
        
        $scope = $page->getPermissions();
        
        if (Mage::getSingleton('admin/session')->isAllowed('cms/page/permissions')) {
            if ($page->getAll() || empty($scope)) {
                $page->setData('permissions', null);
            } else {
                if (is_array($scope)) {
                    $scope = implode(',', $scope);
                }
                $page->setData('permissions', $scope);
            }
        } else {
            $user = Mage::getSingleton('admin/session')->getUser();
            if (!$page->getId() && $user->getScope()) {
                $page->setData('permissions', $user->getScope());
            }
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
            'unl_core_report_product_orderdetails',
            'unl_core_report_product_customized'
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
                    $request->initForward()
                        ->setActionName('denied')
                        ->setDispatched(false);
                }
            }
        }
    }
    
    public function onAdminCmsPageEditPreDispatch($observer)
    {
        $request = Mage::app()->getRequest();
        $user = Mage::getSingleton('admin/session')->getUser();
        
        $id = $request->getParam('page_id');
        $model = Mage::getModel('cms/page');
        
        if ($id) {
            $model->load($id);
            if ($user->getScope() && $model->getPermissions()) {
                $perm = explode(',', $model->getPermissions());
                $scope = explode(',', $user->getScope());
                $allow = false;
                foreach ($scope as $store_id) {
                    $allow = in_array($store_id, $perm);
                    if ($allow) {
                        break;
                    }
                }
                
                if (!$allow) {
                    $request->initForward()
                        ->setActionName('denied')
                        ->setDispatched(false);
                }
            }
        }
    }
    
    public function onAdminSalesCreditmemoViewPreDispatch($observer)
    {
        $this->_onAdminSalesEntityViewPreDispatch($observer, 'creditmemo');
    }
    
    public function onAdminSalesInvoiceViewPreDispatch($observer)
    {
        $this->_onAdminSalesEntityViewPreDispatch($observer, 'invoice');
    }
    
    public function onAdminSalesOrderViewPreDispatch($observer)
    {
        $this->_onAdminSalesEntityViewPreDispatch($observer, 'order');
    }
    
    public function onAdminSalesShipmentViewPreDispatch($observer)
    {
        $this->_onAdminSalesEntityViewPreDispatch($observer, 'shipment');
    }
    
    protected function _onAdminSalesEntityViewPreDispatch($observer, $type)
    {
        $request = Mage::app()->getRequest();
        $user = Mage::getSingleton('admin/session')->getUser();
        /* @var $action Mage_Adminhtml_Controller_Action */
        $action = $observer->getControllerAction();
        
        $param = $type . '_id';
        $model = null;
        switch ($type) {
            case 'creditmemo':
                $model = Mage::getModel('sales/order_creditmemo')->load($request->getParam($param));
                break;
            case 'invoice':
                $model = Mage::getModel('sales/order_invoice')->load($request->getParam($param));
                break;
            case 'order':
                $orderId = $request->getParam('order_id');
                break;
            case 'shipment':
                $model = Mage::getModel('sales/order_shipment')->load($request->getParam($param));
                break;
        }
        if ($model !== null) {
            if (!$model->getId()) {
                $session = Mage::getSingleton('adminhtml/session');
                $session->addError($action->__('The %s no longer exists', $type));
                $session->setIsUrlNotice($action->getFlag('', Mage_Adminhtml_Controller_Action::FLAG_IS_URLS_CHECKED));
                $action->getResponse()->setRedirect($action->getUrl('*/*/'));
                $action->setFlag('', Mage_Adminhtml_Controller_Action::FLAG_NO_DISPATCH, true);
                return;
            } else {
                $orderId = $model->getOrderId();
            }
        }
        
        if (!is_null($user->getScope()) && $request->getParam($param)) {
            $scope = explode(',', $user->getScope());
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $order_items->getSelect()
                ->where('source_store_view IN (?)', $scope)
                ->where('order_id = ?', $orderId);
                
            if (!count($order_items)) {
                $request->initForward()
                    ->setActionName('denied')
                    ->setDispatched(false);
            }
        }
    }
    
    public function onRssOrderNewCollectionSelect($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        $user = Mage::getSingleton('admin/session')->getUser();
        
        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'))
                ->where('source_store_view IN (?)', $scope)
                ->group('order_id');
                
            $collection->getSelect()
                ->joinInner(array('scope' => $select), 'e.entity_id = scope.order_id', array());
        }
    }
}