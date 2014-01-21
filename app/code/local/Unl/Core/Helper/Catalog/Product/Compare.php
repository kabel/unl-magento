<?php

class Unl_Core_Helper_Catalog_Product_Compare extends Mage_Catalog_Helper_Product_Compare
{
    /* Overrides
     * @see Mage_Catalog_Helper_Product_Compare::getItemCollection()
     * so thumbnail attribute is selected
     */
    public function getItemCollection()
    {
        if (!$this->_itemCollection) {
            $this->_itemCollection = Mage::getResourceModel('catalog/product_compare_item_collection')
                ->useProductItem(true)
                ->setStoreId(Mage::app()->getStore()->getId());

            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $this->_itemCollection->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } elseif ($this->_customerId) {
                $this->_itemCollection->setCustomerId($this->_customerId);
            } else {
                $this->_itemCollection->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }

            Mage::getSingleton('catalog/product_visibility')
                ->addVisibleInSiteFilterToCollection($this->_itemCollection);

            /* Price data is added to consider item stock status using price index */
            $this->_itemCollection->addPriceData();

            $this->_itemCollection->addAttributeToSelect('name')
                ->addAttributeToSelect('thumbnail')
                ->addUrlRewrite()
                ->load();

            /* update compare items count */
            $this->_getSession()->setCatalogCompareItemsCount(count($this->_itemCollection));
        }

        return $this->_itemCollection;
    }
}
