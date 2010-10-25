<?php

class Unl_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fetchServerFile($path)
    {
        $host = Mage::helper('core/http')->getHttpHost();
        if (!$host || !preg_match('/unl\.edu$/', $host)) {
            $host = 'www.unl.edu';
        }
        return file_get_contents('http://' . $host . $path);
    }
    
    public function getProductSourceStoreFilterOptions()
    {
        $options = Mage::getSingleton('unl_core/store_source_switcher')->getOptionArray();

        array_unshift($options, array('label'=>$this->__('Any Store'), 'value'=>''));
        return $options;
    }
    
    public function isCustomerAllowedCategory($category, $reload = true)
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
    
    public function isCustomerAllowedProduct($product)
    {
        if ($acl = $product->getProductGroupAcl()) {
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
                /* @var $session Mage_Customer_Model_Session */
                $session = Mage::getSingleton('customer/session');
                if (!$session->isLoggedIn()) {
                    Mage::throwException($this->__('You must be logged in to checkout with this item'));
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
            }
        }
    }
}