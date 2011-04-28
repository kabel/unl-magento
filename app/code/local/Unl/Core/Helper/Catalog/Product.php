<?php

class Unl_Core_Helper_Catalog_Product extends Mage_Catalog_Helper_Product
{
    /**
     * Inits product to be used for product controller actions and layouts
     * $params can have following data:
     *   'category_id' - id of category to check and append to product as current.
     *     If empty (except FALSE) - will be guessed (e.g. from last visited) to load as current.
     *
     * @param int $productId
     * @param Mage_Core_Controller_Front_Action $controller
     * @param Varien_Object $params
     *
     * @return false|Mage_Catalog_Model_Product
     */
    public function initProduct($productId, $controller, $params = null)
    {
        // Prepare data for routine
        if (!$params) {
            $params = new Varien_Object();
        }

        // Init and load product
        Mage::dispatchEvent('catalog_controller_product_init_before', array('controller_action' => $controller));

        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if (!$this->canShow($product)) {
            return false;
        }
        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        // Load product current category
        $categoryId = $params->getCategoryId();
        if (!$categoryId && ($categoryId !== false)) {
            $lastId = Mage::getSingleton('catalog/session')->getLastVisitedCategoryId();
            if ($product->canBeShowInCategory($lastId)) {
                $categoryId = $lastId;
            }
        }

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $product->setCategory($category);
            Mage::register('current_category', $category);
        }

        // Register current data and dispatch final events
        Mage::register('current_product', $product);
        Mage::register('product', $product);

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product' => $product));
            $result = new Varien_Object(array('prevent_default' => false));
            Mage::dispatchEvent('catalog_controller_product_init_after', array(
            	'product' => $product,
                'controller_action' => $controller,
                'result' => $result
            ));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        if ($result->getPreventDefault()) {
            return false;
        }

        return $product;
    }
}
