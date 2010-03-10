<?php

class  Unl_Core_Model_Tax_Mysql4_Report_Collection extends Mage_Tax_Model_Mysql4_Report_Collection
{
    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(period, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM period)';
        } else {
            $this->_periodFormat = 'period';
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'code'                  => "CASE WHEN code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%' THEN CONCAT('US-NE-', RIGHT(code, 14)) ELSE code END",
                'base_sales_amount_sum' => 'sum(base_sales_amount_sum)',
                'percent'               => 'percent',
                'orders_count'          => 'sum(orders_count)',
                'tax_base_amount_sum'   => 'sum(tax_base_amount_sum)'
            );
        }

        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }
    
    protected  function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()
                ->joinLeft(array('pf' => 'unl_tax_places'), "CASE WHEN code LIKE '%-CityFips-%' THEN RIGHT(code, 5) = pf.fips_place_number ELSE NULL END", array('city' => 'name'))
                ->joinLeft(array('cf' => 'unl_tax_counties'), "CASE WHEN code LIKE '%-CountyFips-%' THEN RIGHT(code, 3) = cf.county_id ELSE NULL END", array('county' => 'name'))
                ->group(array(
                    $this->_periodFormat,
                    "CASE WHEN code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%' THEN RIGHT(code, 14) ELSE code END"
                ));
        }

        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }

        return $this;
    }
}