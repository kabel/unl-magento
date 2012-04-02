<?php

class Unl_Core_Block_Catalog_Product_Featured extends Mage_Catalog_Block_Product_Abstract
{
     protected function _construct()
     {
         parent::_construct();

         if (!$this->getTemplate()) {
             $this->setTemplate('catalog/product/featured.phtml');
         }

         if ($this->hasData('minimal')) {
             $this->setWrapperClass('featured minimal');
         } else {
             $this->setWrapperClass('featured');
         }

         if (!$this->getCollectionSize()) {
             $this->setCollectionSize(1);
         }

         $this->_addPriceBlockTypes()
             ->_initCollection();
     }

     public function useMinimalTemplate()
     {
         $this->setWrapperClass('featured minimal');

         return $this;
     }

     public function getProduct()
     {
         $collection = $this->getProductCollection();

         return $collection->getFirstItem();
     }

     public function getProducts()
     {
         return $this->getProductCollection()->getItems();
     }

     protected function _initCollection()
     {
         $storeId = Mage::app()->getStore()->getId();
         $now = Mage::getSingleton('core/date')->gmtDate();

         $collection = $this->_getProductCollection()
             ->setStoreId($storeId)
             ->addAttributeToFilter('source_store_view', $storeId)
             ->addAttributeToFilter('featured_from', array('lteq' => $now))
             ->addAttributeToFilter('featured_to', array('gteq' => $now));

         if (!count($collection->getItems())) {
             $collection = $this->_getProductCollection()
                 ->setStoreId($storeId)
                 ->addAttributeToFilter('source_store_view', $storeId);
         }

         $this->setProductCollection($collection);

         return $this;
     }

     /**
      * Gets the default featured product collection
      *
      * @return Mage_Catalog_Model_Resource_Product_Collection
      */
     protected function _getProductCollection()
     {
         $stock = Mage::getModel('cataloginventory/stock_item');

         /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
         $collection = Mage::getModel('catalog/product')->getCollection()
             ->addAttributeToSelect('*')
             ->addAttributeToFilter('enable_featured', true)
             ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
             ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds());

         $stock->addCatalogInventoryToProductCollection($collection);
         $collection->addFieldToFilter('is_saleable', true);

         $collection->getSelect()->orderRand('e.entity_id');

         $collection->setPageSize($this->getCollectionSize());

         return $collection;
     }

     protected function _addPriceBlockTypes()
     {
         //bundles
         $this->addPriceBlockType('bundle', 'bundle/catalog_product_price', 'bundle/catalog/product/price.phtml');

         return $this;
     }
}
