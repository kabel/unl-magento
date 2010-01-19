<?php

class Unl_Core_Block_Checkout_Links extends Mage_Checkout_Block_Links
{
    /**
     * Add shopping cart link to parent block
     *
     * @return Unl_Core_Block_Checkout_Links
     */
    public function addCartLink()
    {
        if ($parentBlock = $this->getParentBlock()) {
            $count = $this->helper('checkout/cart')->getSummaryCount();

//            if( $count == 1 ) {
//                $text = $this->__('Your Cart (%s item)', $count);
//            } elseif( $count > 0 ) {
//                $text = $this->__('Your Cart (%s items)', $count);
//            } else {
                $text = $this->__('Your Cart');
//            }

            $parentBlock->addLink($text, 'checkout/cart', $text, true, array(), 50, null, 'class="top-link-cart"');
        }
        return $this;
    }
}