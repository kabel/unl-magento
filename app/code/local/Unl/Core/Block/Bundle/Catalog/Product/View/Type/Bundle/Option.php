<?php

class Unl_Core_Block_Bundle_Catalog_Product_View_Type_Bundle_Option extends Mage_Bundle_Block_Catalog_Product_View_Type_Bundle_Option
{
    public function getSelectionQtyTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection);

        $output = $_selection->getSelectionQty()*1 . ' x ' . $_selection->getName();
        if ($price > 0) {
            $output .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">' : '') . '+' .
                $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>' : '');
        }

        return $output;
    }

    public function getSelectionTitlePrice($_selection, $includeContainer = true)
    {
        $price = $this->getProduct()->getPriceModel()->getSelectionPreFinalPrice($this->getProduct(), $_selection, 1);

        $output = $_selection->getName();
        if ($price > 0) {
            $output .= ' &nbsp; ' . ($includeContainer ? '<span class="price-notice">':'') . '+' .
                $this->formatPriceString($price, $includeContainer) . ($includeContainer ? '</span>':'');
        }

        return $output;
    }

    public function formatPriceString($price, $includeContainer = true)
    {
        $taxHelper  = Mage::helper('tax');
        $coreHelper = $this->helper('core');
        if ($this->getFormatProduct()) {
            $product = $this->getFormatProduct();
        } else {
            $product = $this->getProduct();
        }

        $priceTax    = $taxHelper->getPrice($product, $price);
        $priceIncTax = $taxHelper->getPrice($product, $price, true);

        if ($priceTax == 0) {
            return '';
        }

        $formated = $coreHelper->currencyByStore($priceTax, $product->getStore(), true, $includeContainer);
        if ($taxHelper->displayBothPrices() && $priceTax != $priceIncTax) {
            $formated .=
                    ' (+' .
                    $coreHelper->currencyByStore($priceIncTax, $product->getStore(), true, $includeContainer) .
                    ' ' . $this->__('Incl. Tax') . ')';
        }

        return $formated;
    }

    protected function _getSelectionQty($selection)
    {
        if ($this->getProduct()->hasPreconfiguredValues()) {
            $selectedQty = $this->getProduct()->getPreconfiguredValues()
                ->getData('bundle_option_qty/' . $this->getOption()->getId());
            if (isset($selectedQty[$selection->getSelectionId()])) {
                $selectedQty = $selectedQty[$selection->getSelectionId()];
            }
            $selectedQty = (float)$selectedQty;
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        } else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }
}
