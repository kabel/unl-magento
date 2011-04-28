<?php

class Unl_Core_Block_Catalog_Product_Featured_Global extends Unl_Core_Block_Catalog_Product_Featured
{

    protected function _initCollection()
    {
        $product = Mage::getModel('catalog/product');
        $stock   = Mage::getModel('cataloginventory/stock_item');

        /* @var $products Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
        $products = $product->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('enable_featured', true)
            ->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
            ->addAttributeToFilter('visibility', array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH));
        $stock->addCatalogInventoryToProductCollection($products);
        $products->getSelect()->where('(IF(IF(use_config_manage_stock, 1, manage_stock), is_in_stock, 1)) = ?', true);

        $this->setProductCollection($products);

        return $this;
    }

    public function isSourceStoreActive($product) {
        $store_id = $product->getSourceStoreView();
        $stores = Mage::app()->getStores();
        if ($store_id && isset($stores[$store_id]) && $stores[$store_id]->getIsActive()) {
            return true;
        }

        return false;
    }
}
