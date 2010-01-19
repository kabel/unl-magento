<?php

class Unl_Core_Block_Wishlist_Links extends Mage_Wishlist_Block_Links
{
    /**
     * Add link on wishlist page in parent block
     *
     * @return Unl_Core_Block_Wishlist_Links
     */
    public function addWishlistLink()
    {
        $parentBlock = $this->getParentBlock();
        if ($parentBlock && $this->helper('wishlist')->isAllow()) {
            $count = $this->helper('wishlist')->getItemCount();
//            if( $count > 1 ) {
//                $text = $this->__('Your Wishlist (%d items)', $count);
//            } elseif( $count == 1 ) {
//                $text = $this->__('Your Wishlist (%d item)', $count);
//            } else {
                $text = $this->__('Your Wishlist');
//            }
            $parentBlock->addLink($text, 'wishlist', $text, true, array(), 30, null, 'class="top-link-wishlist"');
        }
        return $this;
    }
}