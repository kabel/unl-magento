<?php

class Unl_Core_Block_Catalog_Product_Featured extends Mage_Catalog_Block_Product_Abstract
{
     public function _construct()
     {
         parent::_construct();
         $this->_addPriceBlockTypes()
             ->_initCollection();
     }

     protected function _initCollection()
     {
         $storeId = Mage::app()->getStore()->getId();
         $product = Mage::getModel('catalog/product');
         $stock   = Mage::getModel('cataloginventory/stock_item');

         /* @var $products Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
         $products = $product->getCollection()
             ->setStoreId($storeId)
             ->addAttributeToSelect('*')
             ->addAttributeToSelect(array('featured_from', 'featured_to'), 'left')
             ->addAttributeToFilter('enable_featured', true)
             ->addAttributeToFilter('source_store_view', $storeId)
             ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
             ->addAttributeToFilter('visibility', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH));
         $stock->addCatalogInventoryToProductCollection($products);
         $products->getSelect()->where('(IF(IF(use_config_manage_stock, 1, manage_stock), is_in_stock, 1)) = ?', true);
         $products->getSelect()->where('NOW() BETWEEN _table_featured_from.value AND _table_featured_to.value');

         $pool = $products->getItems();
         if (!count($pool)) {
             $products = $product->getCollection()
                 ->setStoreId($storeId)
                 ->addAttributeToSelect('*')
                 ->addAttributeToFilter('enable_featured', true)
                 ->addAttributeToFilter('source_store_view', $storeId)
                 ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                 ->addAttributeToFilter('visibility', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH));
             $stock->addCatalogInventoryToProductCollection($products);
             $products->getSelect()->where('(IF(IF(use_config_manage_stock, 1, manage_stock), is_in_stock, 1)) = ?', true);
         }

         $this->setProductCollection($products);

         return $this;
     }

     protected function _addPriceBlockTypes()
     {
         //bundles
         $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');

         return $this;
     }
}
