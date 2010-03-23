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
        $store   = $address->getQuote()->getStore();
        $shippingTaxClass   = $this->_config->getShippingTaxClass($store);
        $shippingAmount     = $address->getShippingAmount();
        $baseShippingAmount = $address->getBaseShippingAmount();
        $shippingDiscountAmount = $address->getShippingDiscountAmount();
        $baseShippingDiscountAmount = $address->getBaseShippingDiscountAmount();

        /**
         * Subtract discount before calculate tax amount
         */
        if ($this->_config->applyTaxAfterDiscount($store)) {
            $calcAmount     = $shippingAmount - $shippingDiscountAmount;
            $baseCalcAmount = $baseShippingAmount - $baseShippingDiscountAmount;
        } else {
            $calcAmount     = $shippingAmount;
            $baseCalcAmount = $baseShippingAmount;
        }

        $shippingTax      = 0;
        $shippingBaseTax  = 0;

        if ($shippingTaxClass) {
            $taxRateRequest->setProductClassId($shippingTaxClass);
            $rate = $this->_calculator->getRate($taxRateRequest);
            if ($rate) {
                if ($this->_config->shippingPriceIncludesTax($store) && $this->_areTaxRequestsSimilar) {
                    $shippingTax    = $this->_calculator->calcTaxAmount($calcAmount, $rate, true, false);
                    $shippingBaseTax= $this->_calculator->calcTaxAmount($baseCalcAmount, $rate, true, false);
                    $shippingAmount-= $shippingTax;
                    $baseShippingAmount-=$shippingBaseTax;
                } else {
                    $shippingTax    = $this->_calculator->calcTaxAmount($calcAmount, $rate, false, false);
                    $shippingBaseTax= $this->_calculator->calcTaxAmount($baseCalcAmount, $rate, false, false);
                }
                $rateKey = (string) $rate;
                if (isset($this->_roundingDeltas[$rateKey])) {
                    $shippingTax+= $this->_roundingDeltas[$rateKey];
                }
                if (isset($this->_baseRoundingDeltas[$rateKey])) {
                    $shippingBaseTax+= $this->_baseRoundingDeltas[$rateKey];
                }
                $shippingTax        = $this->_calculator->round($shippingTax);
                $shippingBaseTax    = $this->_calculator->round($shippingBaseTax);

                $address->setTotalAmount('shipping', $shippingAmount);
                $address->setBaseTotalAmount('shipping', $baseShippingAmount);

                /**
                 * Provide additional attributes for apply discount on price include tax
                 */
                if ($this->_config->discountTax($store)) {
                    $address->setShippingAmountForDiscount($shippingAmount+$shippingTax);
                    $address->setBaseShippingAmountForDiscount($baseShippingAmount+$shippingBaseTax);
                }

                $this->_addAmount($shippingTax);
                $this->_addBaseAmount($shippingBaseTax);

                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $shippingTax, $shippingBaseTax, $rate, $shippingAmount, $baseShippingAmount);
            }
        }
        $address->setShippingTaxAmount($shippingTax);
        $address->setBaseShippingTaxAmount($shippingBaseTax);

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
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
            if ($item->getParentItemId()) {
                continue;
            }
            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $rate = $this->_calculator->getRate(
                        $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId())
                    );
                    $this->_calcRowTaxAmount($child, $rate);
                    $this->_addAmount($child->getTaxAmount());
                    $this->_addBaseAmount($child->getBaseTaxAmount());

                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate, $child->getRowTotal(), $child->getBaseRowTotal());
                }
                $this->_recalculateParent($item);
            }
            else {
                $rate = $this->_calculator->getRate(
                    $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
                $this->_calcRowTaxAmount($item, $rate);
                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());

                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getRowTotal(), $item->getBaseRowTotal());
            }
        }
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
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
            if ($item->getParentItemId()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $rate = $this->_calculator->getRate(
                        $taxRateRequest->setProductClassId($child->getProduct()->getTaxClassId())
                    );
                    $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_aggregateTaxPerRate($child, $rate, $taxGroups);
                }
                $this->_recalculateParent($item);
            } else {
                $rate = $this->_calculator->getRate(
                    $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId())
                );
                $taxGroups[(string)$rate]['applied_rates'] = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
            }
        }

        $inclTax = $this->_usePriceIncludeTax($store);
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
            /**
             * Child item's tax we calculate for parent - that why we skip them
             */
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

                    $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                    $this->_saveAppliedTaxes($address, $applied, $child->getTaxAmount(), $child->getBaseTaxAmount(), $rate, $child->getRowTotal(), $child->getBaseRowTotal());
                }
                $this->_recalculateParent($item);
            }
            else {
                $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
                $rate = $this->_calculator->getRate($taxRateRequest);

                $this->_calcUnitTaxAmount($item, $rate);

                $this->_addAmount($item->getTaxAmount());
                $this->_addBaseAmount($item->getBaseTaxAmount());

                $applied = $this->_calculator->getAppliedRates($taxRateRequest);
                $this->_saveAppliedTaxes($address, $applied, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getRowTotal(), $item->getBaseRowTotal());
            }
        }
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