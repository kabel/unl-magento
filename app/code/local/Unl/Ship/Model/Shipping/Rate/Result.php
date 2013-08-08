<?php

class Unl_Ship_Model_Shipping_Rate_Result extends Mage_Shipping_Model_Rate_Result
{
    public function sortRatesByPrice()
    {
        if (!is_array($this->_rates) || !count($this->_rates)) {
            return $this;
        }

        usort($this->_rates, array($this, '_compareRatesByPrice'));
        return $this;
    }

    /**
     * Compare rate result methods
     *
     * @param Mage_Shipping_Model_Rate_Result_Method $a
     * @param Mage_Shipping_Model_Rate_Result_Method $b
     */
    protected function _compareRatesByPrice($a, $b)
    {
        $priceA = $a->getPrice();
        $priceB = $b->getPrice();

        if ($priceA == $priceB) {
            return 0;
        }

        return ($priceA < $priceB) ? -1 : 1;
    }
}
