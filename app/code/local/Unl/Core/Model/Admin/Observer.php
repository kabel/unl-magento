<?php

class Unl_Core_Model_Admin_Observer
{
    /**
     * An <i>adminhtml</i> event observer for the <code>admin_user_authenticate_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function preventNonCasLogin($observer)
    {
        /* @var $user Mage_Admin_Model_User */
        $user = $observer->getEvent()->getUser();
        $result = $observer->getEvent()->getResult();

        if ($result && $user->getIsCas()) {
            Mage::throwException(Mage::helper('adminhtml')->__('Invalid Username or Password.'));
        }
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>admin_user_save_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
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

    /**
     * An <i>adminhtml</i> event observer for the <code>cms_page_save_before</code> event.
     *
     * @param Varien_Event_Observer $observer
     */
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

    /**
     * An <i>adminhtml</i> event observer for the <code>adminhtml_catalog_category_tree_can_add_root_category</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function isAllowedAddRootCategory($observer)
    {
        $options = $observer->getEvent()->getOptions();
        if (Mage::helper('unl_core')->getAdminUserScope()) {
            $options->setIsAllow(false);
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the <code>controller_action_layout_render_before_adminhtml_permissions_user_edit</code>
     * event. Adds another tab to the controller generated content.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeRenderPermissionsLayout($observer)
    {
        /* @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getSingleton('core/layout');
        $block  = $layout->getBlock('left');

        foreach ($block->getChild() as $child) {
            if ($child instanceof Mage_Adminhtml_Block_Permissions_User_Edit_Tabs) {
                $child->addTab('scope_section', array(
                    'label'     => Mage::helper('adminhtml')->__('User Scope'),
                    'title'     => Mage::helper('adminhtml')->__('User Scope'),
                    'content'   => $layout->createBlock('unl_core/adminhtml_permissions_user_edit_tab_scope')->toHtml(),
                    'after'     => 'roles_section',
                ));
                break;
            }
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event listener for the
     * <code>adminhtml_widget_grid_filter_collection</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Admin_Observer
     */
    public function onAdminhtmlWidgetGridFilterCollection($observer)
    {
        /* @var $controller Mage_Core_Controller_Varien_Action */
        /* @var $collection Mage_Reports_Model_Resource_Report_Collection */
        $controller = Mage::app()->getFrontController()->getAction();
        $collection = $observer->getEvent()->getCollection();

        $actions = array(
            'adminhtml_report_customer_accounts',
            'adminhtml_report_customer_totals',
            'adminhtml_report_customer_orders',
            'adminhtml_report_product_sold',
            'adminhtml_report_product_viewed',
        );

        if (in_array($controller->getFullActionName(), $actions)) {
            $collection->setStoreIds(Mage::helper('unl_core')->getScopeFilteredStores($collection->getStoreIds()));
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the following events:
     * <ul>
     *   <li><code>controller_action_predispatch_adminhtml_catalog_category_edit</code></li>
     *   <li><code>controller_action_predispatch_adminhtml_catalog_category_tree</code></li>
     * </ul>
     * Forces an id request param if a user is scoped and an id is missing
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Admin_Observer
     */
    public function onCatalogCategoryPreDispatch($observer)
    {
        /* @var $controller Mage_Adminhtml_Catalog_CategoryController */
        $controller = $observer->getEvent()->getControllerAction();
        $checkParent = $controller->getFullActionName() == 'adminhtml_catalog_category_edit';

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($controller->getRequest()->getParam('store'));
        if (!empty($storeIds) && $storeIds[0] == -1) {
            $controller->getRequest()->setParam('store', false);
        }

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

    /**
     * An <i>adminhtml</i> event observer for the following events:
     * <ul>
     *   <li><code>controller_action_predispatch_adminhtml_report_sales_coupons</code></li>
     *   <li><code>controller_action_predispatch_adminhtml_report_sales_sales</code></li>
     * </ul>
     * Forces a store_ids request param if a user is scoped
     *
     * @param Varien_Event_Observer $observer
     */
    public function onSalesReportPreDispatch($observer)
    {
        $reqeust = $observer->getEvent()->getControllerAction()->getRequest();

        $storeIds = $reqeust->getParam('store_ids');
        if (!empty($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        if (!empty($storeIds)) {
            $reqeust->setParam('store_ids', implode(',', $storeIds));
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the <code>controller_action_predispatch_adminhtml_system_account_save</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Admin_Observer
     */
    public function onSystemAccountSavePreDispatch($observer)
    {
        /* @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $userId = Mage::getSingleton('admin/session')->getUser()->getId();
        $user = Mage::getModel('admin/user')->load($userId);

        if ($user->getIsCas()) {
            $controller->getRequest()->setParam('username', $user->getUsername());
            $controller->getRequest()->setParam('password', false);
            $controller->getRequest()->setParam('confirmation', false);
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event listener for the <code>controller_action_predispatch_adminhtml_catalog_product_edit</code>
     * event. Adds ACL checks for the edited product.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Admin_Observer
     */
    public function onCatalogProductEditPreDispatch($observer)
    {
        /* @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();

        if ($productId = (int) $request->getParam('id')) {
            $product = Mage::getModel('catalog/product')->load($productId);
            if ($product->getId() && !Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
                $request->initForward()
                    ->setActionName('denied')
                    ->setDispatched(false);
            }
        }

        return $this;
    }


    /**
     * An <i>adminhtml</i> event listener for the <code>controller_action_predispatch_adminhtml_cms_page_edit</code>
     * event. Adds ACL checks for the edited page.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Admin_Observer
     */
    public function onAdminCmsPageEditPreDispatch($observer)
    {
        /* @var $controller Mage_Core_Controller_Varien_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();

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

    /**
     * An <i>adminhtml</i> event observer for the following events:
     * <ul>
     *   <li><code>controller_action_predispatch_adminhtml_sales_creditmemo_view</code></li>
     *   <li><code>controller_action_predispatch_adminhtml_sales_invoice_view</code></li>
     *   <li><code>controller_action_predispatch_adminhtml_sales_order_view</code></li>
     *   <li><code>controller_action_predispatch_adminhtml_sales_shipment_view</code></li>
     * </ul>
     * Enforces the ACL constraints for each entity.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAdminSalesEntityViewPreDispatch($observer)
    {
        /* @var $action Mage_Adminhtml_Controller_Action */
        $action  = $observer->getControllerAction();
        $request = $action->getRequest();

        if (!preg_match('/^adminhtml_sales_(creditmemo|invoice|order|shipment)_view$/', $action->getFullActionName(), $matches)) {
            return;
        }

        $type = $matches[1];
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

        if ($request->getParam($param) && Mage::helper('unl_core')->getAdminUserScope()) {
            /* @var $colleciton Mage_Sales_Model_Resource_Order_Item_Collection */
            $collection = Mage::getModel('sales/order_item')->getCollection();

            Mage::helper('unl_core')->addProductAdminScopeFilters($collection);
            $collection->addFieldToFilter('order_id', $orderId);

            if (!count($collection)) {
                $request->initForward()
                    ->setActionName('denied')
                    ->setDispatched(false);
            }
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the
     * <code>rss_catalog_notify_stock_collection_select</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onRssCatalogStockCollectionSelect($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);
    }

    /**
     * An <i>adminhtml</i> event listener for the
     * <code>rss_order_new_collection_select</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onRssOrderNewCollectionSelect($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        Mage::helper('unl_core')->addAdminScopeFilters($collection, 'entity_id', true);
    }
}
