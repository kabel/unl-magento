<?php

class Unl_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CUSTOMER_ALLOWED_PRODUCT_SUCCESS       = 0;
    const CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN = 1;
    const CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL   = 2;
    
    const CUSTOMER_ALLOWED_CATEGORY_SUCCESS       = 0;
    const CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN = 1;
    const CUSTOMER_ALLOWED_CATEGORY_FAILURE_ACL   = 2;
    
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
    
    public function isCustomerAllowedCategory($category, $addNotice=false, $reload=true, $action=null)
    {
        $_cat = $category;
        if (!($category instanceof Mage_Catalog_Model_Category)) {
            $_cat = Mage::getModel('catalog/category')->load($category->getId());
        } else if ($reload) {
            $_cat->load($_cat->getId());
        }
        
        $result = $this->_getCustomerAllowedCategory($_cat);
        switch ($result) {
            case self::CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN:
                if ($addNotice) {
                    Mage::getSingleton('core/session')->addNotice('You must be logged in and authorized to access this part of the catalog');
                }
                if ($action) {
                    Mage::getSingleton('customer/session')->setBeforeAuthUrl($_cat->getUrl());
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
                Mage::getSingleton('core/session')->addNotice('You are not authorized to access this product');
                break;
        }
        return ($result == self::CUSTOMER_ALLOWED_PRODUCT_SUCCESS);
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
        
        if ($acl = $product->getProductGroupAcl()) {
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
                    Mage::getSingleton('core/session')->addNotice($e->getMessage());
                    $checkout = Mage::getSingleton('checkout/session');
                    $checkout->setConsume(true);
                    $checkout->setRedirectUrl(Mage::getUrl('customer/account/login'));
                }
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
}