<?php

class Unl_Core_Model_Tax_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax
{
    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_calculateShippingTaxByRate()
     * to allow saving the calculated total
     */
    protected function _calculateShippingTaxByRate(
        Mage_Sales_Model_Quote_Address $address, $rate, $appliedRates, $taxId = null)
    {
        $inclTax = $address->getIsShippingInclTax();
        $shipping = $address->getShippingTaxable();
        $baseShipping = $address->getBaseShippingTaxable();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $hiddenTax = null;
        $baseHiddenTax = null;
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc = $shipping;
                $baseCalc = $baseShipping;
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $calc = $shipping - $discountAmount;
                $baseCalc = $baseShipping - $baseDiscountAmount;
                break;
        }

        $tax = $this->_calculator->calcTaxAmount($calc, $rate, $inclTax, false);
        $baseTax = $this->_calculator->calcTaxAmount($baseCalc, $rate, $inclTax, false);

        if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            $tax = $this->_deltaRound($tax, $rateKey, $inclTax);
            $baseTax = $this->_deltaRound($baseTax, $rateKey, $inclTax, 'base');
        } else {
            $tax = $this->_calculator->round($tax);
            $baseTax = $this->_calculator->round($baseTax);
        }
        $this->_addAmount(max(0, $tax));
        $this->_addBaseAmount(max(0, $baseTax));

        if ($inclTax && !empty($discountAmount)) {
            $taxBeforeDiscount = $this->_calculator->calcTaxAmount(
                $shipping,
                $rate,
                $inclTax,
                false
            );
            $baseTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                $baseShipping,
                $rate,
                $inclTax,
                false
            );
            if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
                $taxBeforeDiscount =
                    $this->_deltaRound($taxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount');
                $baseTaxBeforeDiscount =
                    $this->_deltaRound($baseTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount_base');
            } else {
                $taxBeforeDiscount = $this->_calculator->round($taxBeforeDiscount);
                $baseTaxBeforeDiscount = $this->_calculator->round($baseTaxBeforeDiscount);
            }
            $hiddenTax = max(0, $taxBeforeDiscount - max(0, $tax));
            $baseHiddenTax = max(0, $baseTaxBeforeDiscount - max(0, $baseTax));
            $this->_hiddenTaxes[] = array(
                'rate_key' => $rateKey,
                'value' => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax' => $inclTax,
            );
        }

        $address->setShippingTaxAmount($address->getShippingTaxAmount() + max(0, $tax));
        $address->setBaseShippingTaxAmount($address->getBaseShippingTaxAmount() + max(0, $baseTax));
        $this->_saveAppliedTaxes($address, $appliedRates, $tax, $baseTax, $rate, $calc, $baseCalc);
    }

    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_calculateShippingTax()
     * to add custom logic for tax free items
     */
    protected function _calculateShippingTax(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $taxRateRequest->setProductClassId($this->_determineShippingTaxClass($address));
        $rate = $this->_calculator->getRate($taxRateRequest);
        $inclTax = $address->getIsShippingInclTax();

        $address->setShippingTaxAmount(0);
        $address->setBaseShippingTaxAmount(0);
        $address->setShippingHiddenTaxAmount(0);
        $address->setBaseShippingHiddenTaxAmount(0);
        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        if ($inclTax) {
            $this->_calculateShippingTaxByRate($address, $rate, $appliedRates);
        } else {
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $taxId = $appliedRate['id'];
                $this->_calculateShippingTaxByRate($address, $taxRate, array($appliedRate), $taxId);
            }
        }
        return $this;
    }

    protected function _determineShippingTaxClass(Mage_Sales_Model_Quote_Address $address)
    {
        foreach ($address->getAllItems() as $item) {
            if ($item->getTaxAmount() > 0) {
                return $this->_config->getShippingTaxClass($this->_store);
            }
        }

        return $this->_config->getExemptShippingTaxClass($this->_store);
    }


    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_unitBaseProcessItemTax()
     * by saving the calculated tax amount
     */
    protected function _unitBaseProcessItemTax(
        $address, $item, $taxRateRequest, &$itemTaxGroups, $catalogPriceInclTax
    )
    {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($taxRateRequest);

        $item->setTaxAmount(0);
        $item->setBaseTaxAmount(0);
        $item->setHiddenTaxAmount(0);
        $item->setBaseHiddenTaxAmount(0);
        $item->setTaxPercent($rate);
        $item->setDiscountTaxCompensation(0);
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $recalculateRowTotalInclTax = false;
        if (!isset($rowTotalInclTax)) {
            $qty = $item->getTotalQty();
            $item->setRowTotalInclTax($this->_store->roundPrice($item->getTaxableAmount() * $qty));
            $item->setBaseRowTotalInclTax(
                $this->_store->roundPrice($item->getBaseTaxableAmount() * $qty));
            $recalculateRowTotalInclTax = true;
        }

        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        $item->setTaxRates($appliedRates);
        if ($catalogPriceInclTax) {
            $this->_calcUnitTaxAmount($item, $rate);
            $this->_saveAppliedTaxes(
                $address, $appliedRates, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getTaxCalcAmount(), $item->getBaseTaxCalcAmount());
        } else {
            //need to calculate each tax separately
            $taxGroups = array();
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $this->_calcUnitTaxAmount($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
                $this->_saveAppliedTaxes(
                    $address, array($appliedTax), $taxGroups[$taxId]['tax'], $taxGroups[$taxId]['base_tax'], $taxRate, $item->getTaxCalcAmount(), $item->getBaseTaxCalcAmount());
            }
            //We need to calculate weeeAmountInclTax using multiple tax rate here
            //because the _calculateWeeeTax and _calculateRowWeeeTax only take one tax rate
            if ($this->_weeeHelper->isEnabled() && $this->_weeeHelper->isTaxable()) {
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, false);
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, true);
            }
        }
        $itemTaxGroups[$item->getId()] = $appliedRates;
        $this->_addAmount($item->getTaxAmount());
        $this->_addBaseAmount($item->getBaseTaxAmount());
        return;
    }

    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_calcUnitTaxAmount()
     * by saving the calculation amount
     */
    protected function _calcUnitTaxAmount(
        $item, $rate, &$taxGroups = null, $taxId = null, $recalculateRowTotalInclTax = false
    )
    {
        $qty = $item->getTotalQty();
        $inclTax = $item->getIsPriceInclTax();
        $price = $item->getTaxableAmount();
        $basePrice = $item->getBaseTaxableAmount();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $isWeeeEnabled = $this->_weeeHelper->isEnabled();
        $isWeeeTaxable = $this->_weeeHelper->isTaxable();

        $hiddenTax = null;
        $baseHiddenTax = null;
        $weeeTax = null;
        $baseWeeeTax = null;
        $unitTaxBeforeDiscount = null;
        $weeeTaxBeforeDiscount = null;
        $baseUnitTaxBeforeDiscount = null;
        $baseWeeeTaxBeforeDiscount = null;

        switch ($this->_config->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc = $price;
                $baseCalc = $basePrice;
                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false);

                if ($isWeeeEnabled && $isWeeeTaxable) {
                    $weeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate, false);
                    $unitTaxBeforeDiscount += $weeeTaxBeforeDiscount;
                    $baseWeeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate);
                    $baseUnitTaxBeforeDiscount += $baseWeeeTaxBeforeDiscount;
                }
                $unitTaxBeforeDiscount = $unitTax = $this->_calculator->round($unitTaxBeforeDiscount);
                $baseUnitTaxBeforeDiscount = $baseUnitTax = $this->_calculator->round($baseUnitTaxBeforeDiscount);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount() / $qty;
                $baseDiscountAmount = $item->getBaseDiscountAmount() / $qty;

                //We want to remove weee
                if ($isWeeeEnabled) {
                    $discountAmount = $discountAmount - $item->getWeeeDiscount() / $qty;
                    $baseDiscountAmount = $baseDiscountAmount - $item->getBaseWeeeDiscount() / $qty;
                }

                $calc = max($price - $discountAmount, 0);
                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $unitTaxDiscount = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
                $unitTax = $this->_calculator->round(max($unitTaxBeforeDiscount - $unitTaxDiscount, 0));

                $baseCalc = max($basePrice - $baseDiscountAmount, 0);
                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false);
                $baseUnitTaxDiscount = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false);
                $baseUnitTax = $this->_calculator->round(max($baseUnitTaxBeforeDiscount - $baseUnitTaxDiscount, 0));

                if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                    $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                    $weeeTax = $weeeTax / $qty;
                    $unitTax += $weeeTax;
                    $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                    $baseWeeeTax = $baseWeeeTax / $qty;
                    $baseUnitTax += $baseWeeeTax;
                }

                $unitTax = $this->_calculator->round($unitTax);
                $baseUnitTax = $this->_calculator->round($baseUnitTax);

                //Calculate the weee taxes before discount
                $weeeTaxBeforeDiscount = 0;
                $baseWeeeTaxBeforeDiscount = 0;

                if ($isWeeeTaxable) {
                    $weeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate, false);
                    $unitTaxBeforeDiscount += $weeeTaxBeforeDiscount;
                    $baseWeeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate);
                    $baseUnitTaxBeforeDiscount += $baseWeeeTaxBeforeDiscount;
                }

                $unitTaxBeforeDiscount = max(0, $this->_calculator->round($unitTaxBeforeDiscount));
                $baseUnitTaxBeforeDiscount = max(0, $this->_calculator->round($baseUnitTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $unitTaxBeforeDiscount - $unitTax;
                    $baseHiddenTax = $baseUnitTaxBeforeDiscount - $baseUnitTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                } elseif ($discountAmount > $price) { // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $price;
                    $baseHiddenTax = $baseDiscountAmount - $basePrice;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                }
                // calculate discount compensation
                // We need the discount compensation when dont calculate the hidden taxes
                // (when product does not include taxes)
                if (!$item->getNoDiscount() && $item->getWeeeTaxApplied()) {
                    $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                        $unitTaxBeforeDiscount * $qty - max(0, $unitTax) * $qty);
                }
                break;
        }

        $item->setTaxCalcAmount($qty * $calc);
        $item->setBaseCalcAmount($qty * $baseCalc);

        $rowTax = $this->_store->roundPrice(max(0, $qty * $unitTax));
        $baseRowTax = $this->_store->roundPrice(max(0, $qty * $baseUnitTax));
        $item->setTaxAmount($item->getTaxAmount() + $rowTax);
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + $baseRowTax);
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($price * $qty);
                $item->setBaseRowTotalInclTax($basePrice * $qty);
            } else {
                $item->setRowTotalInclTax(
                    $item->getRowTotalInclTax() + ($unitTaxBeforeDiscount - $weeeTaxBeforeDiscount) * $qty);
                $item->setBaseRowTotalInclTax(
                    $item->getBaseRowTotalInclTax() +
                    ($baseUnitTaxBeforeDiscount - $baseWeeeTaxBeforeDiscount) * $qty);
            }
        }

        return $this;
    }

    protected function _rowBaseProcessItemTax($address, $item, $taxRateRequest, &$itemTaxGroups, $catalogPriceInclTax)
    {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($taxRateRequest);

        $item->setTaxAmount(0);
        $item->setBaseTaxAmount(0);
        $item->setHiddenTaxAmount(0);
        $item->setBaseHiddenTaxAmount(0);
        $item->setTaxPercent($rate);
        $item->setDiscountTaxCompensation(0);
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $recalculateRowTotalInclTax = false;
        if (!isset($rowTotalInclTax)) {
            $item->setRowTotalInclTax($item->getTaxableAmount());
            $item->setBaseRowTotalInclTax($item->getBaseTaxableAmount());
            $recalculateRowTotalInclTax = true;
        }

        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        $item->setTaxRates($appliedRates);
        if ($catalogPriceInclTax) {
            $this->_calcRowTaxAmount($item, $rate);
            $this->_saveAppliedTaxes(
                $address, $appliedRates, $item->getTaxAmount(), $item->getBaseTaxAmount(), $rate, $item->getTaxCalcAmounT(), $item->getBaseTaxCalcAmount());
        } else {
            //need to calculate each tax separately
            $taxGroups = array();
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $this->_calcRowTaxAmount($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
                $this->_saveAppliedTaxes(
                    $address, array($appliedTax), $taxGroups[$taxId]['tax'], $taxGroups[$taxId]['base_tax'], $taxRate, $item->getTaxCalcAmounT(), $item->getBaseTaxCalcAmount());
            }
            //We need to calculate weeeAmountInclTax using multiple tax rate here
            //because the _calculateWeeeTax and _calculateRowWeeeTax only take one tax rate
            if ($this->_weeeHelper->isEnabled() && $this->_weeeHelper->isTaxable()) {
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, false);
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, true);
            }
        }
        $itemTaxGroups[$item->getId()] = $appliedRates;
        $this->_addAmount($item->getTaxAmount());
        $this->_addBaseAmount($item->getBaseTaxAmount());
        return;
    }

    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_calcRowTaxAmount()
     * by saving the calculated tax amount
     */
    protected function _calcRowTaxAmount(
        $item, $rate, &$taxGroups = null, $taxId = null, $recalculateRowTotalInclTax = false
    )
    {
        $inclTax = $item->getIsPriceInclTax();
        $subtotal = $taxSubtotal = $item->getTaxableAmount();
        $baseSubtotal = $baseTaxSubtotal = $item->getBaseTaxableAmount();
        $rateKey = ($taxId == null) ? (string)$rate : $taxId;

        $isWeeeEnabled = $this->_weeeHelper->isEnabled();
        $isWeeeTaxable = $this->_weeeHelper->isTaxable();

        $hiddenTax = null;
        $baseHiddenTax = null;
        $weeeTax = null;
        $baseWeeeTax = null;
        $rowTaxBeforeDiscount = null;
        $baseRowTaxBeforeDiscount = null;
        $weeeRowTaxBeforeDiscount = null;
        $baseWeeeRowTaxBeforeDiscount = null;

        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $calc     = $subtotal;
                $baseCalc = $baseSubtotal;
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false);

                if ($isWeeeEnabled && $isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                }
                $rowTaxBeforeDiscount = $rowTax = $this->_calculator->round($rowTaxBeforeDiscount);
                $baseRowTaxBeforeDiscount = $baseRowTax = $this->_calculator->round($baseRowTaxBeforeDiscount);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount();
                $baseDiscountAmount = $item->getBaseDiscountAmount();

                if ($isWeeeEnabled) {
                    $discountAmount = $discountAmount - $item->getWeeeDiscount();
                    $baseDiscountAmount = $baseDiscountAmount - $item->getBaseWeeeDiscount();
                }

                $calc     = max($subtotal - $discountAmount, 0);
                $baseCalc = max($baseSubtotal - $baseDiscountAmount, 0);

                $rowTax = $this->_calculator->calcTaxAmount(
                    max($subtotal - $discountAmount, 0),
                    $rate,
                    $inclTax
                );
                $baseRowTax = $this->_calculator->calcTaxAmount(
                    max($baseSubtotal - $baseDiscountAmount, 0),
                    $rate,
                    $inclTax
                );

                if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                    $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                    $rowTax += $weeeTax;
                    $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                    $baseRowTax += $baseWeeeTax;
                }

                $rowTax = $this->_calculator->round($rowTax);
                $baseRowTax = $this->_calculator->round($baseRowTax);

                //Calculate the Row Tax before discount
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $subtotal,
                    $rate,
                    $inclTax,
                    false
                );
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $baseSubtotal,
                    $rate,
                    $inclTax,
                    false
                );

                //Calculate the Weee taxes before discount
                $weeeRowTaxBeforeDiscount = 0;
                $baseWeeeRowTaxBeforeDiscount = 0;
                if ($isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                }

                $rowTaxBeforeDiscount = max(0, $this->_calculator->round($rowTaxBeforeDiscount));
                $baseRowTaxBeforeDiscount = max(0, $this->_calculator->round($baseRowTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $rowTaxBeforeDiscount - $rowTax;
                    $baseHiddenTax = $baseRowTaxBeforeDiscount - $baseRowTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                } elseif ($discountAmount > $subtotal) { // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $subtotal;
                    $baseHiddenTax = $baseDiscountAmount - $baseSubtotal;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                }
                // calculate discount compensation
                if (!$item->getNoDiscount() && $item->getWeeeTaxApplied()) {
                    $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                        $rowTaxBeforeDiscount - max(0, $rowTax));
                }
                break;
        }
        $item->setTaxCalcAmount($calc);
        $item->setBaseTaxCalcAmount($baseCalc);

        $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($subtotal);
                $item->setBaseRowTotalInclTax($baseSubtotal);
            } else {
                $item->setRowTotalInclTax(
                    $item->getRowTotalInclTax() + $rowTaxBeforeDiscount - $weeeRowTaxBeforeDiscount);
                $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() +
                    $baseRowTaxBeforeDiscount - $baseWeeeRowTaxBeforeDiscount);
            }
        }
        return $this;
    }

    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_totalBaseCalculation()
     * by saving the calculated tax amount
     */
    protected function _totalBaseCalculation(Mage_Sales_Model_Quote_Address $address, $taxRateRequest)
    {
        $items = $this->_getAddressItems($address);
        $store = $address->getQuote()->getStore();
        $taxGroups = array();
        $itemTaxGroups = array();
        $catalogPriceInclTax = $this->_config->priceIncludesTax($store);

        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            if ($item->getHasChildren() && $item->isChildrenCalculated()) {
                foreach ($item->getChildren() as $child) {
                    $this->_totalBaseProcessItemTax(
                        $child, $taxRateRequest, $taxGroups, $itemTaxGroups, $catalogPriceInclTax);
                }
                $this->_recalculateParent($item);
            } else {
                $this->_totalBaseProcessItemTax(
                    $item, $taxRateRequest, $taxGroups, $itemTaxGroups, $catalogPriceInclTax);
            }
        }

        if ($address->getQuote()->getTaxesForItems()) {
            $itemTaxGroups += $address->getQuote()->getTaxesForItems();
        }
        $address->getQuote()->setTaxesForItems($itemTaxGroups);

        foreach ($taxGroups as $taxId => $data) {
            if ($catalogPriceInclTax) {
                $rate = (float)$taxId;
            } else {
                $rate = $data['applied_rates'][0]['percent'];
            }

            $inclTax = $data['incl_tax'];

            $total = array_sum($data['totals']);
            $baseTotal = array_sum($data['base_totals']);
            $totalTax = array_sum($data['tax']);
            $baseTotalTax = array_sum($data['base_tax']);
            $this->_addAmount($totalTax);
            $this->_addBaseAmount($baseTotalTax);
            $totalTaxRounded = $this->_calculator->round($totalTax);
            $baseTotalTaxRounded = $this->_calculator->round($totalTaxRounded);
            $this->_saveAppliedTaxes($address, $data['applied_rates'], $totalTaxRounded, $baseTotalTaxRounded, $rate, $total, $baseTotal);
        }
        return $this;
    }

    /* Overrides
     * @see Mage_Tax_Model_Sales_Total_Quote_Tax::_totalBaseProcessItemTax()
     * by keeping 0% tax rates (reconciliation)
     */
    protected function _totalBaseProcessItemTax(
        $item, $taxRateRequest, &$taxGroups, &$itemTaxGroups, $catalogPriceInclTax
    )
    {
        $taxRateRequest->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($taxRateRequest);

        $item->setTaxAmount(0);
        $item->setBaseTaxAmount(0);
        $item->setHiddenTaxAmount(0);
        $item->setBaseHiddenTaxAmount(0);
        $item->setTaxPercent($rate);
        $item->setDiscountTaxCompensation(0);
        $rowTotalInclTax = $item->getRowTotalInclTax();
        $recalculateRowTotalInclTax = false;
        if (!isset($rowTotalInclTax)) {
            $item->setRowTotalInclTax($item->getTaxableAmount());
            $item->setBaseRowTotalInclTax($item->getBaseTaxableAmount());
            $recalculateRowTotalInclTax = true;
        }

        $appliedRates = $this->_calculator->getAppliedRates($taxRateRequest);
        if ($catalogPriceInclTax) {
            $taxGroups[(string)$rate]['applied_rates'] = $appliedRates;
            $taxGroups[(string)$rate]['incl_tax'] = $item->getIsPriceInclTax();
            $this->_aggregateTaxPerRate($item, $rate, $taxGroups);
        } else {
            //need to calculate each tax separately
            foreach ($appliedRates as $appliedTax) {
                $taxId = $appliedTax['id'];
                $taxRate = $appliedTax['percent'];
                $taxGroups[$taxId]['applied_rates'] = array($appliedTax);
                $taxGroups[$taxId]['incl_tax'] = $item->getIsPriceInclTax();
                $this->_aggregateTaxPerRate($item, $taxRate, $taxGroups, $taxId, $recalculateRowTotalInclTax);
            }

            //We need to calculate weeeAmountInclTax using multiple tax rate here
            //because the _calculateWeeeTax and _calculateRowWeeeTax only take one tax rate
            if ($this->_weeeHelper->isEnabled() && $this->_weeeHelper->isTaxable()) {
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, false);
                $this->_calculateWeeeAmountInclTax($item, $appliedRates, true);
            }
        }
        $itemTaxGroups[$item->getId()] = $appliedRates;
        return;
    }

    /**
     * @param   Mage_Sales_Model_Quote_Address $address
     * @param   array $applied
     * @param   float $amount
     * @param   float $baseAmount
     * @param   float $rate
     * @param   float $saleAmount [OPTIONAL] The total used to calculate the amount
     * @param   float $baseSaleAmount [OPTIONAL] The base total used to calculate the amount
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

                $appliedAmount = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }


            if ($saleAmount || $previouslyAppliedTaxes[$row['id']]['sale_amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;

                $previouslyAppliedTaxes[$row['id']]['sale_amount'] += $saleAmount;
                $previouslyAppliedTaxes[$row['id']]['base_sale_amount'] += $baseSaleAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
    }
}
