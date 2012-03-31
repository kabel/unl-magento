<?php

class Unl_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    const TAX_EXEMPT_ORG = 'Exempt Org';
    const TAX_GROUP_EXEMPT_ORG = 'Tax Exempt Org';

    const CUSTOMER_ALLOWED_PRODUCT_SUCCESS       = 0;
    const CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN = 1;
    const CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL   = 2;

    const CUSTOMER_ALLOWED_CATEGORY_SUCCESS       = 0;
    const CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN = 1;
    const CUSTOMER_ALLOWED_CATEGORY_FAILURE_ACL   = 2;

    protected $_taxCodeCases = array(
        "code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%'" => "CONCAT('US-NE-', RIGHT(code, 14))",
        "code LIKE '%-CityFips+-%'" => "CONCAT('US-NE-CityFips-', SUBSTRING(code, LOCATE('-CityFips+-', code) + 11))",
    );

    protected $_cityFipsCases = array(
        "code LIKE '%-CityFips-%'" => "RIGHT(code, 5) = pf.fips_place_number",
        "code LIKE '%-CityFips+-%'" => "SUBSTRING(code, LOCATE('-CityFips+-', code) + 11, 5) = pf.fips_place_number",
    );

    protected $_countyFipsCases = array(
        "code LIKE '%-CountyFips-%'" => "RIGHT(code, 3) = cf.county_id",
    );

    public function fetchServerFile($path)
    {
        $path = '/' . ltrim($path, '/');
        $root = Mage::app()->getRequest()->getServer('DOCUMENT_ROOT');

        if (is_file($root . $path)) {
            return file_get_contents($root . $path);
        }

        return false;
    }

    public function getProductSourceStoreFilterOptions()
    {
        $options = Mage::getSingleton('unl_core/store_source_switcher')->getOptionArray();

        array_unshift($options, array('label'=>$this->__('Any Store'), 'value'=>''));
        return $options;
    }

    public function getActivePaymentMethodOptions($withEmpty = true)
    {
        if ($withEmpty) {
            $options = array('' => '');
        } else {
            $options = array();
        }

        $methods = Mage::getSingleton('payment/config')->getActiveMethods();
        foreach ($methods as $code => $method) {
            $options[$code] = $method->getTitle() ? $method->getTitle() : $code;
        }

        return $options;
    }

    public function getAdminUserScope($asGroups = false)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $scope = $user->getScope();
        if (!$scope) {
            return false;
        }

        if (!is_array($scope)) {
            $scope = explode(',', $scope);
        }

        if (!$asGroups) {
            return $scope;
        }

        $groupScope = array();
        foreach ($scope as $storeId) {
            try {
                $group = Mage::app()->getStore($storeId)->getGroup();
                if (!in_array($group->getId(), $groupScope)) {
                    $groupScope[] = $group->getId();
                }
            } catch (Exception $e) {
                // ignore
            }
        }

        return $groupScope;
    }

    public function getAdminUserWarehouseScope($asArray = false)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        $scope = $user->getWarehouseScope();
        if (!$scope) {
            return false;
        }

        if ($asArray) {
            return explode(',', $scope);
        }

        return $scope;
    }

    /**
     * Adds the admin user scope filters to a sales collection
     *
     * @param Varien_Data_Collection_Db $collection
     * @param string $joinColumn
     * @param boolean $withState
     *
     * @return Varien_Db_Select
     */
    public function addAdminScopeFilters($collection, $joinColumn = 'order_id', $withState = false, $storeIds = null)
    {
        $select = null;
        $storeIds = $this->getScopeFilteredStores($storeIds);

        if (!empty($storeIds)) {
            /* @var $order_items Mage_Sales_Model_Resource_Order_Item_Collection */
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            $adapter = $order_items->getConnection();

            $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'))
                ->group('order_id')
                ->where($adapter->prepareSqlCondition('source_store_view', array('in' => $storeIds)));

            if ($whScope = $this->getAdminUserWarehouseScope()) {
                if ($withState) {
                    $collection->addFieldToFilter('state', array('nin' => Mage::getModel('unl_core/warehouse')->getFilterStates()));
                }

                $select->where($adapter->prepareSqlCondition('warehouse', array('in' => $whScope)));
            }

            $collection->getSelect()
                ->join(array('scope' => $select), 'main_table.' . $joinColumn . ' = scope.order_id', array());
        }

        return $select;
    }

    /**
     * Add the admin user scope filters to a product collection
     *
     * @param Mage_Catalog_Model_Resource_Product_Collection $collection
     * @param array|int $storeIds
     */
    public function addProductAdminScopeFilters($collection, $storeIds = null)
    {
        $storeIds = $this->getScopeFilteredStores($storeIds);

        if (!empty($storeIds)) {
            $collection->addAttributeToFilter('source_store_view', array('in' => $storeIds));

            if ($whScope = $this->getAdminUserWarehouseScope()) {
                $collection->addAttributeToFilter('warehouse', array('in' => $whScope));
            }
        }

        return $this;
    }

    /**
     * Returns a optionally filtered admin user scope
     *
     * @param array|null $storeIds
     * @return mixed
     */
    public function getScopeFilteredStores($storeIds = null)
    {
        if ($scope = $this->getAdminUserScope()) {
            if (!empty($storeIds)) {
                if (!is_array($storeIds)) {
                    $storeIds = array($storeIds);
                }

                $storeIds = array_intersect($scope, $storeIds);

                // ensure the return isn't empty, return impossible value
                if (empty($storeIds)) {
                    $storeIds = array(-1);
                }
            } else {
                $storeIds = $scope;
            }
        }

        return $storeIds;
    }

    public function isCustomerAllowedCategory($category, $addNotice=false, $reload=true, $action=null)
    {
        $result = $this->_getCustomerAllowedCategory($category);
        switch ($result) {
            case self::CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN:
                if ($addNotice) {
                    Mage::getSingleton('core/session')->addNotice('You must be logged in and authorized to access this part of the catalog');
                }
                if ($action) {
                    Mage::getSingleton('customer/session')->setBeforeAuthUrl($this->getCategoryUrl($category));
                    $action->getResponse()->setRedirect($this->_getUrl('customer/account/login'));
                }
                break;
            case self::CUSTOMER_ALLOWED_CATEGORY_FAILURE_ACL:
                if ($addNotice) {
                    Mage::getSingleton('core/session')->addNotice('You are not authorized to access this part of the catalog');
                }
                break;
        }

        return ($result == self::CUSTOMER_ALLOWED_CATEGORY_SUCCESS);
    }

    public function getCategoryUrl($category)
    {
        if ($category instanceof Mage_Catalog_Model_Category) {
            $url = $category->getUrl();
        } else {
            $url = Mage::getModel('catalog/category')
                ->setData($category->getData())
                ->getUrl();
        }

        return $url;
    }

    protected function _getCustomerAllowedCategory($category) {
        if ($acl = $category->getGroupAcl()) {
            $session = Mage::getSingleton('customer/session');
            if (!empty($acl) && !$session->isLoggedIn()) {
                return self::CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN;
            }

            $customer = $session->getCustomer();
            if (!in_array($customer->getGroupId(), $acl)) {
                return self::CUSTOMER_ALLOWED_CATEGORY_FAILURE_ACL;
            }
        }

        $result = new Varien_Object(array('failure' => 0));
        Mage::dispatchEvent('unl_category_acl_check', array(
        	'category' => $category,
            'result' => $result
        ));

        if ($result->getFailure()) {
            return $result->getFailure();
        }

        return self::CUSTOMER_ALLOWED_CATEGORY_SUCCESS;
    }

    protected function _getCustomerAllowedProduct($product)
    {
        if ($acl = $product->getProductGroupAcl()) {
            $session = Mage::getSingleton('customer/session');
            if (!empty($acl) && !$session->isLoggedIn()) {
                return self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN;
            }

            $customer = $session->getCustomer();
            if (!in_array($customer->getGroupId(), $acl)) {
                return self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL;
            }
        }

        $result = new Varien_Object(array('failure' => 0));
        Mage::dispatchEvent('unl_product_acl_check', array(
        	'product' => $product,
            'result' => $result
        ));

        if ($result->getFailure()) {
            return $result->getFailure();
        }

        return self::CUSTOMER_ALLOWED_PRODUCT_SUCCESS;
    }

    public function isCustomerAllowedProduct($product, $action=null)
    {
        $result = $this->_getCustomerAllowedProduct($product);
        switch ($result) {
            case self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN:
                Mage::getSingleton('core/session')->addNotice('You must be logged in and authorized to access this product');
                if ($action) {
                    Mage::getSingleton('customer/session')->setBeforeAuthUrl($product->getProductUrl());
                    $action->getResponse()->setRedirect($this->_getUrl('customer/account/login'));
                }
                break;
            case self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL:
                if (!Mage::getSingleton('core/session')->getSilenceNextAcl(true)) {
                    Mage::getSingleton('core/session')->addError('You are not authorized to access this product');
                }
                break;
        }
        return ($result == self::CUSTOMER_ALLOWED_PRODUCT_SUCCESS);
    }

    /**
     * Checks the current admin user's scope to see if they have permission to edit
     * the given product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isAdminUserAllowedProductEdit($product)
    {
        $user = Mage::getSingleton('admin/session')->getUser();
        if ($scope = $user->getScope()) {
            $scope = explode(',', $scope);
            $source = $product->getSourceStoreView();
            if ($source && !in_array($source, $scope)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if the product's security should disable the sale
     *
     * @param Mage_Sales_Model_Quote_Item $item
     */
    public function checkCustomerAllowedProduct($item)
    {
        $productId = $item->getProduct()->getId();
        $product = Mage::getModel('catalog/product')->load($productId);

        try {
            $doRedirect = false;
            switch ($this->_getCustomerAllowedProduct($product)) {
                case self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN:
                    $doRedirect = true;
                    Mage::throwException($this->__('You must be logged in and authorized to order this product'));
                    break;
                case self::CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL:
                    Mage::throwException($this->__('You are not authorized to order this product'));
                    break;
            }
        } catch (Mage_Core_Exception $e) {
            $item->setMessage($e->getMessage())
                ->setHasError(true);
            if ($item->getParentItem()) {
                $item->getParentItem()->setMessage($e->getMessage());
            }

            $item->getQuote()->setHasError(true)
                ->addMessage($this->__('Some of the products cannot be ordered because you are not authorized'), 'security');

            if ($doRedirect) {
                $url = $this->_getUrl('customer/account/login');
                $checkout = Mage::getSingleton('checkout/session');
                $checkout->setConsume(true);
                if (Mage::app()->getRequest()->getActionName() == 'add') {
                    throw $e;
                }

                if (Mage::app()->getRequest()->getActionName() != 'login') {
                    Mage::getSingleton('core/session')->addNotice($this->__('You must be logged in and authoried to order an item in your cart'));
                    Mage::app()->getResponse()->setRedirect($url);
                }
            } else {
                Mage::getSingleton('core/session')->setSilenceNextAcl(true);
            }
        }
    }

    /**
     *
     * Check if the product limits the Qty that can ever be purchased
     *
     * @param Mage_Sales_Model_Quote_Item $item
     */
    public function checkCustomerAllowedProductQty($item)
    {
        $productId = $item->getProduct()->getId();
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($limit = $product->getLimitSaleQty()) {
            try {
                $doRedirect = false;
                /* @var $session Mage_Customer_Model_Session */
                $session = Mage::getSingleton('customer/session');
                if (!$session->isLoggedIn()) {
                    Mage::throwException($this->__('You must be logged in to order this product'));
                }

                $rowQty = $item->getQty();
                if ($item->getParentItem()) {
                    $rowQty *= $item->getParentItem()->getQty();
                }

                if (!is_numeric($limit)) {
                    $limit = Mage::app()->getLocale()->getNumber($limit);
                }
                $stockItem = $item->getProduct()->getStockItem();
                if (!$stockItem->getIsQtyDecimal()) {
                    $limit = intval($limit);
                }


                /* @var $orderItems Mage_Sales_Model_Mysql4_Order_Item_Collection */
                $orderItems = Mage::getModel('sales/order_item')->getCollection();
                $orderItems->addFieldToFilter('product_id', $product->getId())
                    ->join('order', 'order.entity_id=main_table.order_id AND order.customer_id=' . $session->getCustomer()->getId(), array());

                $soldQty = 0;
                foreach ($orderItems as $orderItem) {
                    $soldQty += $orderItem->getQtyOrdered() - $orderItem->getQtyCanceled();
                }

                if (($rowQty + $soldQty) > $limit) {
                    $text = $this->__('You may only order %s of this product.', $limit);
                    if ($rowQty) {
                        $text .= ' ' . $this->__('You have %s in your cart.', $rowQty);
                    }
                    if ($soldQty) {
                        $text .= ' ' . $this->__('You have previously ordered %s.', $soldQty);
                    }
                    Mage::throwException($text);
                }
            } catch (Mage_Core_Exception $e) {
                $item->setMessage($e->getMessage())
                    ->setHasError(true);
                if ($item->getParentItem()) {
                    $item->getParentItem()->setMessage($e->getMessage());
                }

                $item->getQuote()->setHasError(true)
                    ->addMessage($this->__('Some of the products cannot be ordered in the requested quantity'), 'qty');

                if ($doRedirect) {
                    Mage::app()->getResponse()->setRedirect($this->_getUrl('customer/account/login'));
                }
            }
        }
    }

    public function getAdvancedGridFilters($type, $clear=false)
    {
        $session = Mage::getSingleton('adminhtml/session');

        switch ($type) {
            case 'order':
            case 'customer':
                return $session->getData($this->getAdvancedGridFiltersStorageKey($type), $clear);
        }

        return false;
    }

    public function getAdvancedGridFiltersStorageKey($type)
    {
        switch ($type) {
            case 'order':
            case 'customer':
                return $type . 'Gridadvfilter';
        }

        return false;
    }

    public function getTaxCodeCases()
    {
        return $this->_taxCodeCases;
    }

    public function getCityFipsCases()
    {
        return $this->_cityFipsCases;
    }

    public function getCountyFipsCases()
    {
        return $this->_countyFipsCases;
    }

    /**
     * Returns the store_ids of all the items in the quote
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return array
     */
    public function getStoresFromQuote($quote)
    {
        return $this->getStoresFromItems($quote->getAllItems());
    }

    /**
     * Returns the store_ids of all the items
     *
     * @param array $items
     * @return array
     */
    public function getStoresFromItems($items)
    {
        $stores = array();

        foreach ($items as $item) {
            if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
                $item = $item->getQuoteItem();
            }

            if ($item->getParentItem()) {
                continue;
            }

            if (!in_array($item->getSourceStoreView(), $stores)) {
                $stores[] = $item->getSourceStoreView();
            }
        }

        return $stores;
    }

    /**
     * Check for quote items used for dummy calculations
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @return boolean
     */
    protected function _isDummyQuoteItem($quoteItem)
    {
        if ($quoteItem->getHasChildren() && $quoteItem->isChildrenCalculated()) {
            return true;
        }

        if ($quoteItem->getHasChildren() && !$quoteItem->isChildrenCalculated()) {
            return false;
        }

        if ($quoteItem->getParentItem() && $quoteItem->isChildrenCalculated()) {
            return false;
        }

        if ($quoteItem->getParentItem() && !$quoteItem->isChildrenCalculated()) {
            return true;
        }

        return false;
    }
}
