<?php

class Unl_Core_Block_Catalog_Product_Price extends Mage_Catalog_Block_Product_Price
{
    public function getTierPrices($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $prices = $product->getFormatedTierPrice();

        $res = array();
        $i = 0;
        if (is_array($prices)) {
            foreach ($prices as $price) {
                $price['price_qty'] = $price['price_qty'] * 1;

                $productPrice = $product->getPrice();
                if ($product->getPrice() != $product->getFinalPrice()) {
                    $productPrice = $product->getFinalPrice();
                }

                // Group price must be used for percent calculation if it is lower
                $groupPrice = $product->getGroupPrice();
                if ($productPrice > $groupPrice) {
                    $productPrice = $groupPrice;
                }

                if ($price['price'] < $productPrice) {
                    $price['savePercent'] = ceil(100 - ((100 / $productPrice) * $price['price']));

                    $tierPrice = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['website_price'])
                    );
                    $price['formated_price'] = '<span class="price tier-' . $i . '">' .
                        Mage::app()->getStore()->formatPrice($tierPrice, false) .
                        '</span>';
                    $tierPriceInclTax = Mage::app()->getStore()->convertPrice(
                        Mage::helper('tax')->getPrice($product, $price['website_price'], true)
                    );
                    $price['formated_price_incl_tax'] = '<span class="price tier-' . $i . '-incl-tax">' .
                        Mage::app()->getStore()->formatPrice($tierPriceInclTax, false) .
                        '</span>';

                    if (Mage::helper('catalog')->canApplyMsrp($product)) {
                        $oldPrice = $product->getFinalPrice();
                        $product->setPriceCalculation(false);
                        $product->setPrice($tierPrice);
                        $product->setFinalPrice($tierPrice);

                        $this->getLayout()->getBlock('product.info')->getPriceHtml($product);
                        $product->setPriceCalculation(true);

                        $price['real_price_html'] = $product->getRealPriceHtml();
                        $product->setFinalPrice($oldPrice);
                    }

                    $res[$i++] = $price;
                }
            }
        }

        return $res;
    }
}
