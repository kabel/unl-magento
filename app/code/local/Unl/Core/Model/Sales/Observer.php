<?php


class Unl_Core_Model_Sales_Observer
{
    public function setQuoteItemSourceStore($event)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $event['product'];
        /* @var $quote_item Mage_Sales_Model_Quote_Item */
        $quote_item = $event['quote_item'];
        
        $sourceStore = $product->getSourceStoreView();
        
        if (null !== $sourceStore) {
            $quote_item->setSourceStoreView($sourceStore);
        }
    }
}