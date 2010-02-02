<?php

class Unl_Core_Block_Bundle_Catalog_Product_View_Type_Bundle_Option extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
{
    public function getSelectionQtyTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection);
        
        $output = $_selection->getSelectionQty()*1 . ' x ' . $_selection->getName() . ' &nbsp; ';
        if ($price > 0) {
            $output .= ($includeContainer ? '<span class="price-notice">':'') . '+' .
                $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>':'');
        }
        
        return $output;
    }

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);
        
        $output = $_selection->getName() . ' &nbsp; ';
        if ($price > 0) { 
            $output .= ($includeContainer ? '<span class="price-notice">':'') . '+' .
                $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>':'');
        }
        
        return $output;
    }

    public function formatPriceString($price, $includeContainer = true)
    {
        $priceTax = Mage::helper('tax')->getPrice($this->getProduct(), $price);
        $priceIncTax = Mage::helper('tax')->getPrice($this->getProduct(), $price, true);
        
        if ($priceTax == 0) {
            return '';
        }

        if (Mage::helper('tax')->displayBothPrices() && $priceTax != $priceIncTax) {
            $formated = Mage::helper('core')->currency($priceTax, true, $includeContainer);
            $formated .= ' (+'.Mage::helper('core')->currency($priceIncTax, true, $includeContainer).' '.Mage::helper('tax')->__('Incl. Tax').')';
        } else {
            $formated = $this->helper('core')->currency($priceTax, true, $includeContainer);
        }

        return $formated;
    }
}