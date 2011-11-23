<?php

class Unl_Core_Block_Wishlist_Customer_Wishlist extends Mage_Wishlist_Block_Customer_Wishlist
{
    /* Overrides
     * @see Mage_Wishlist_Block_Abstract::getWishlistItems()
     * by removing the current store filter
     */
    public function getWishlistItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getWishlist()
                ->getItemCollection()
                ->addStoreFilter($this->getSharedStoreIds());
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    public function getSharedStoreIds($current = true)
    {
        if ($current) {
            return Mage::app()->getStore()->getWebsite()->getStoreIds();
        } else {
            $_storeIds = array();
            $stores = Mage::app()->getStores();
            foreach ($stores as $store) {
                $_storeIds[] = $store->getId();
            }
            return $_storeIds;
        }
    }
}
