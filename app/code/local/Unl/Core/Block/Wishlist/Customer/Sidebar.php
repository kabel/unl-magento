<?php

class Unl_Core_Block_Wishlist_Customer_Sidebar extends Mage_Wishlist_Block_Customer_Sidebar
{
    /* Overrides
     * @see Mage_Wishlist_Block_Abstract::getWishlistItems()
     * by removing the current store filter
     */
    public function getWishlistItems()
    {
        if (is_null($this->_collection)) {
            $this->_collection = $this->_getWishlist()
                ->getItemCollection();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }
}
