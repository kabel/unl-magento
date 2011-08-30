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

        $warehouseScope = $user->getWarehouseScope();
        if ($user->getWarehouseNone() || empty($warehouseScope)) {
            $user->setData('warehouse_scope', null);
        } else {
            if (is_array($warehouseScope)) {
                $warehouseScope = implode(',', $warehouseScope);
            }
            $user->setData('warehouse_scope', $warehouseScope);
        }
    }

    public function preventNonCasLogin($observer)
    {
        /* @var $user Mage_Admin_Model_User */
        $user = $observer->getEvent()->getUser();
        $result = $observer->getEvent()->getResult();

        if ($result && $user->getIsCas()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid Username or Password.'));
        }
    }

    public function filterCasUsersFromForgotPassword($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        if ($collection instanceof Mage_Admin_Model_Mysql4_User_Collection && Mage::app()->getRequest()->getActionName() == 'forgotpassword') {
            $collection->addFieldToFilter('is_cas', false);
        }
    }

    public function clearSimpleCasSession($observer)
    {
        unset($_SESSION['__SIMPLECAS_TICKET']);
        unset($_SESSION['__SIMPLECAS_UID']);
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
            $userScope = Mage::helper('unl_core')->getAdminUserScope();
            if (!$page->getId() && $userScope) {
                $page->setData('permissions', implode(',', $userScope));
            }
        }
    }

    public function isAllowedAddRootCategory($observer)
    {
        $options = $observer->getEvent()->getOptions();
        if (Mage::helper('unl_core')->getAdminUserScope()) {
            $options->setIsAllow(false);
        }
    }

    public function controllerActionPredispatch($observer)
    {
        $storeIdActions = array(
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
        );
        $storeIdsActions = array(
            'adminhtml_report_sales_sales',
            'adminhtml_report_sales_coupons',
            'unl_core_report_product_orderdetails',
            'unl_core_report_product_customized',
        	'unl_core_report_sales_reconcile_cc',
        	'unl_core_report_sales_reconcile_co',
            'unl_core_report_sales_reconcile_nocap',
            'shippingoverride_report_index',
        );
        $categoryIdActions = array(
            'adminhtml_catalog_category_edit',
            'adminhtml_catalog_category_tree',
        );

        $controller = $observer->getEvent()->getControllerAction();
        $actionName = $controller->getFullActionName();
        if (in_array($actionName, $storeIdActions)) {
            $this->_setStoreParamFromUser('store');
            return;
        } elseif (in_array($actionName, $storeIdsActions)) {
            $this->_setStoreParamFromUser('store_ids');
            return;
        } elseif (in_array($actionName, $categoryIdActions)) {
            $checkParent = $controller->getFullActionName() == 'adminhtml_catalog_category_edit';
            return $this->_forceCategoryId($controller, $checkParent);
        }

        if ($actionName == 'adminhtml_system_account_save') {
            $user = Mage::getModel('admin/user')
                ->load(Mage::getSingleton('admin/session')->getUser()->getId());
            if ($user->getIsCas()) {
                $controller->getRequest()->setParam('username', $user->getUsername());
                $controller->getRequest()->setParam('password', false);
                $controller->getRequest()->setParam('confirmation', false);
            }
            return;
        }
    }

    /**
     * Forces an id request param if a user is scoped and an id is missing
     *
     * @param Mage_Adminhtml_Catalog_CategoryController $controller
     */
    protected function _forceCategoryId($controller, $checkParent = false)
    {
        $this->_setStoreParamFromUser('store', false);
        $categoryId = (int) $controller->getRequest()->getParam('id');
        $parentId = (int) $controller->getRequest()->getParam('parent');
        $scope = Mage::helper('unl_core')->getAdminUserScope(true);
        if ($scope && !$categoryId) {
            if ($checkParent && $parentId) {
                return $this;
            }
            $controller->getRequest()->setParam('id', Mage::app()->getGroup(current($scope))->getRootCategoryId());
        }

        return $this;
    }

    protected function _setStoreParamFromUser($param, $forceSet = true) {
        $request = Mage::app()->getRequest();

        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            if ($store = $request->getParam($param)) {
                if (!in_array($store, $scope)) {
                    $request->setParam($param, current($scope));
                }
            } else if ($forceSet) {
                $request->setParam($param, current($scope));
            }
        }
    }

    public function catalogProductEditActionPreDispatch($observer)
    {
        $request = Mage::app()->getRequest();

        if ($productId = (int) $request->getParam('id')) {
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId()) {
                if (!Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
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

        $id = $request->getParam('page_id');
        $model = Mage::getModel('cms/page');

        if ($id) {
            $model->load($id);
            $scope = Mage::helper('unl_core')->getAdminUserScope();
            if ($scope && $model->getPermissions()) {
                $perm = explode(',', $model->getPermissions());
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

        $scope = Mage::helper('unl_core')->getAdminUserScope();
        if ($scope && $request->getParam($param)) {
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $order_items->getSelect()
                ->where('source_store_view IN (?)', $scope)
                ->where('order_id = ?', $orderId);

            $whScope = Mage::helper('unl_core')->getAdminUserWarehouseScope();
            if ($whScope) {
                $order_items->getSelect()->where('warehouse IN (?)', $whScope);
            }

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
        Mage::helper('unl_core')->addAdminScopeFilters($collection, 'order_id', true);
    }

    public function onRssCatalogStockCollectionSelect($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);
    }
}
