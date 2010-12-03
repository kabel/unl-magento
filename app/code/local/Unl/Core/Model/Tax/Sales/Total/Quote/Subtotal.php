<?php

/**
 * Calculate items and address amounts including/excluding tax
 */
class Unl_Core_Model_Tax_Sales_Total_Quote_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal
{
    /**
     * Recalculate row information for item based on children calculation
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @return  Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _recalculateParent(Mage_Sales_Model_Quote_Item_Abstract $item)
    {
        $price       = 0;
        $basePrice   = 0;
        $rowTotal    = 0;
        $baseRowTotal= 0;
        $priceInclTax       = 0;
        $basePriceInclTax   = 0;
        $rowTotalInclTax    = 0;
        $baseRowTotalInclTax= 0;
        foreach ($item->getChildren() as $child) {
            $price              += $child->getOriginalPrice() * $child->getQty();
            $basePrice          += $child->getBaseOriginalPrice() * $child->getQty();
            $rowTotal           += $child->getRowTotal();
            $baseRowTotal       += $child->getBaseRowTotal();
            $priceInclTax       += $child->getPriceInclTax() * $child->getQty();
            $basePriceInclTax   += $child->getBasePriceInclTax() * $child->getQty();
            $rowTotalInclTax    += $child->getRowTotalInclTax();
            $baseRowTotalInclTax+= $child->getBaseRowTotalInclTax();
        }
        $item->setOriginalPrice($price);
        $item->setPrice($basePrice);
        $item->setRowTotal($rowTotal);
        $item->setBaseRowTotal($baseRowTotal);
        $item->setPriceInclTax($priceInclTax);
        $item->setBasePriceInclTax($basePriceInclTax);
        $item->setRowTotalInclTax($rowTotalInclTax);
        $item->setBaseRowTotalInclTax($baseRowTotalInclTax);
        return $this;
    }
}
