<?php

class Unl_Core_Model_Tax_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /**
     * Tax caclulation for shipping price
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $taxRateRequest->setProductClassId($this->_config->getShippingTaxClass($this->_store));
        $rate               = $this->_calculator->getRate($taxRateRequest);
        $inclTax            = $address->getIsShippingInclTax();
        $shipping           = $address->getShippingTaxable();
        $baseShipping       = $address->getBaseShippingTaxable();
        
        $hiddenTax     = null;
        $baseHiddenTax = null;
        
        $shippingDiscountAmount = $address->getShippingDiscountAmount();
        $baseShippingDiscountAmount = $address->getBaseShippingDiscountAmount();
        
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc     = $shipping;
                $baseCalc = $baseShipping;
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $calc     = $shipping - $discountAmount;
                $baseCalc = $baseShipping - $baseDiscountAmount;
                break;
        }
        
        $tax     = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax, false);
        $baseTax = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax, false);
        
        if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            $tax        = $this->_deltaRound($tax, $rate, $inclTax);
            $baseTax    = $this->_deltaRound($baseTax, $rate, $inclTax, 'base');
        } else {
            $tax        = $this->_calculator->round($tax);
            $baseTax    = $this->_calculator->round($baseTax);
        }
        if ($inclTax && !empty($discountAmount)) {
            $hiddenTax      = $shipping - $tax - $address->getShippingAmount();
            $baseHiddenTax  = $baseShipping - $baseTax - $address->getBaseShippingAmount();
        }

        $this->_addAmount(max(0, $tax));
        $this->_addBaseAmount(max(0, $baseTax));
        $address->setShippingTaxAmount(max(0, $tax));
        $address->setBaseShippingTaxAmount(max(0, $baseTax));
        $address->setShippingHiddenTaxAmount(max(0, $hiddenTax));
        $address->setBaseShippingHiddenTaxAmount(max(0, $baseHiddenTax));
        $address->addTotalAmount('shipping_hidden_tax', $hiddenTax);
        $address->addBaseTotalAmount('shipping_hidden_tax', $baseHiddenTax);
        $applied = $this->_calculator->getAppliedRates($taxRateRequest);
        $this->_saveAppliedTaxes($address, $applied, $tax, $baseTax, $rate, $calc, $baseCalc);
        return $this;
    }
    
    /**
     * Calculate address total tax based on row total
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _rowBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items  = $address->getAllItems();
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $this->_calcRowTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());
                    $this->_getAddress()->addTotalAmount('hidden_tax', $child->getHiddenTaxAmount());
                    $this->_getAddress()->addBaseTotalAmount('hidden_tax', $child->getBaseHiddenTaxAmount());
                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate, $child->getTaxCalcAmount(), $child->getBaseTaxCalcAmount());
                }
                $this->_recalculateParent($item);
            }
            else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $this->_calcRowTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());
                $this->_getAddress()->addTotalAmount('hidden_tax', $item->getHiddenTaxAmount());
                $this->_getAddress()->addBaseTotalAmount('hidden_tax', $item->getBaseHiddenTaxAmount());
                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getTaxCalcAmount(), $item->getBaseTaxCalcAmount());
            }
        }
        return $this;
    }
    
    /**
     * Calculate item tax amount based on row total
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $rate
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calcRowTaxAmount($item, $rate)
    {
        $inclTax        = $item->getIsPriceInclTax();
        $subtotal       = $item->getTaxableAmount() + $item->getExtraRowTaxableAmount();
        $baseSubtotal   = $item->getBaseTaxableAmount() + $item->getBaseExtraRowTaxableAmount();
        $item->setTaxPercent($rate);

        $hiddenTax     = null;
        $baseHiddenTax = null;
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc     = $subtotal;
                $baseCalc = $baseSubtotal;
                $rowTax     = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $item->getDiscountAmount();
                $baseDiscountAmount = $item->getBaseDiscountAmount();
                $calc     = $subtotal - $discountAmount;
                $baseCalc = $baseSubtotal - $baseDiscountAmount;
                $rowTax     = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax);
                if ($inclTax && $discountAmount>0) {
                    $hiddenTax      = $subtotal - $rowTax - $item->getRowTotal();
                    $baseHiddenTax  = $baseSubtotal - $baseRowTax - $item->getBaseRowTotal();
                }
                break;
        }
        
        $item->setTaxCalcAmount($calc);
        $item->setBaseTaxCalcAmount($baseCalc);
        $item->setTaxAmount(max(0, $rowTax));
        $item->setBaseTaxAmount(max(0, $baseRowTax));
        $item->setHiddenTaxAmount(max(0, $hiddenTax));
        $item->setBaseHiddenTaxAmount(max(0, $baseHiddenTax));
        return $this;
    }
    
    /**
     * Calculate address total tax based on address subtotal
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   Varien_Object $taxRateRequest
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _totalBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items      = $address->getAllItems();
        $store      = $address->getQuote()->getStore();
        $taxGroups  = array();

        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_aggregateTaxPerRate($child, $rate, $taxGroups);
                    $this->_getAddress()->addTotalAmount('hidden_tax', $child->getHiddenTaxAmount());
                    $this->_getAddress()->addBaseTotalAmount('hidden_tax', $child->getBaseHiddenTaxAmount());
                    $inclTax = $child->getIsPriceInclTax();
                }
                $this->_recalculateParent($item);
            } else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
                $this->_getAddress()->addTotalAmount('hidden_tax', $item->getHiddenTaxAmount());
                $this->_getAddress()->addBaseTotalAmount('hidden_tax', $item->getBaseHiddenTaxAmount());
                $inclTax = $item->getIsPriceInclTax();
            }
        }

        foreach ($taxGroups as $rateKey => $data) {
            $rate = (float) $rateKey;
            $total = array_sum($data['totals']);
            $baseTotal = array_sum($data['base_totals']);
            $totalTax = $this->_calculator->calcTaxAmount($total, $rate, $inclTax);
            $baseTotalTax = $this->_calculator->calcTaxAmount($baseTotal, $rate, $inclTax);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTax, $baseTotalTax, $rate, $total, $baseTotal);
        }
        return $this;
    }
    
    /**
     * Calculate address tax amount based on one unit price and tax amount
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _unitBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items  = $address->getAllItems();
        foreach ($items as $item) {
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId());
                    $rate = $this->_calculator->getRate($taxRateRequest);
                    $this->_calcUnitTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());
                    $this->_getAddress()->addTotalAmount('hidden_tax', $child->getHiddenTaxAmount());
                    $this->_getAddress()->addBaseTotalAmount('hidden_tax', $child->getBaseHiddenTaxAmount());
                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate, $child->getTaxCalcAmount(), $child->getBaseTaxCalcAmount());
                }
                $this->_recalculateParent($item);
            }
            else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);
                $this->_calcUnitTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());
                $this->_getAddress()->addTotalAmount('hidden_tax', $item->getHiddenTaxAmount());
                $this->_getAddress()->addBaseTotalAmount('hidden_tax', $item->getBaseHiddenTaxAmount());
                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getTaxCalcAmount(), $item->getBaseTaxCalcAmount());
            }
        }
        return $this;
    }
    
    /**
     * Calculate unit tax anount based on unit price
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $rate
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calcUnitTaxAmount(Mage_Sales_Model_Quote_Item_Abstract $item, $rate)
    {
        $extra      = $item->getExtraTaxableAmount();
        $baseExtra  = $item->getBaseExtraTaxableAmount();
        $qty        = $item->getTotalQty();
        $inclTax    = $item->getIsPriceInclTax();
        $price      = $item->getTaxableAmount();
        $basePrice  = $item->getBaseTaxableAmount();
        $item->setTaxPercent($rate);

        $hiddenTax     = null;
        $baseHiddenTax = null;
        switch ($this->_config->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc     = $price;
                $baseCalc = $basePrice;
                $unitTax            = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax);
                $baseUnitTax        = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax);
                break;
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount     = $item->getDiscountAmount() / $qty;
                $baseDiscountAmount = $item->getBaseDiscountAmount() / $qty;
                $calc     = max($price-$discountAmount, 0);
                $baseCalc = max($basePrice-$baseDiscountAmount, 0);
                $unitTax        = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax);
                $baseUnitTax    = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax);
                if ($inclTax && $discountAmount>0) {
                    $hiddenTax      = $price - $unitTax - $item->getConvertedPrice();
                    $baseHiddenTax  = $basePrice - $unitTax - $item->getBasePrice();
                } elseif ($discountAmount > $price) { // case with 100% discount on price incl. tax
                    $hiddenTax      = $discountAmount - $price;
                    $baseHiddenTax  = $baseDiscountAmount - $basePrice;
                }
                break;
        }
        
        $item->setTaxCalcAmount($qty*$calc);
        $item->setBaseTaxCalcAmount($qty*$baseCalc);
        $item->setTaxAmount($this->_store->roundPrice(max(0, $qty*$unitTax)));
        $item->setBaseTaxAmount($this->_store->roundPrice(max(0, $qty*$baseUnitTax)));
        $item->setHiddenTaxAmount(max(0, $qty*$hiddenTax));
        $item->setBaseHiddenTaxAmount(max(0, $qty*$baseHiddenTax));
        return $this;
    }
    
    /**
     * Collect applied tax rates information on address level
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   array $applied
     * @param   float $amount
     * @param   float $baseAmount
     * @param   float $rate
     */
    protected function _saveAppliedTaxes(Mage_Sales_Model_Quote_Address $address, $applied, $amount, $baseAmount, $rate, $saleAmount = 0, $baseSaleAmount = 0)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process'] = $process;
                $row['amount'] = 0;
                $row['base_amount'] = 0;
                $row['sale_amount'] = 0;
                $row['base_sale_amount'] = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate = $rate ? $rate : 1;

                $appliedAmount = $amount/$rate*$row['percent'];
                $baseAppliedAmount = $baseAmount/$rate*$row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }

            $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
            $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            
            $previouslyAppliedTaxes[$row['id']]['sale_amount'] += $saleAmount;
            $previouslyAppliedTaxes[$row['id']]['base_sale_amount'] += $baseSaleAmount;
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
}