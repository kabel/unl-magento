<?php

class  Unl_Core_Model_Tax_Mysql4_Report_Collection extends Mage_Tax_Model_Mysql4_Report_Collection
{
    protected $_codeCases = array(
        "WHEN code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%' THEN CONCAT('US-NE-', RIGHT(code, 14))",
        "WHEN code LIKE '%-CityFips+-%' THEN CONCAT('US-NE-CityFips-', SUBSTRING(code, LOCATE('-CityFips+-', code) + 11))",
    );

    protected $_cityFipsCases = array(
        "WHEN code LIKE '%-CityFips-%' THEN RIGHT(code, 5) = pf.fips_place_number",
        "WHEN code LIKE '%-CityFips+-%' THEN SUBSTRING(code, LOCATE('-CityFips+-', code) + 11, 5) = pf.fips_place_number",
    );

    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Report_Collection::_getSelectedColumns()
     * by changing/adding standard columns
     */
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
                // changed/new columns
                'code'                  => 'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END',
                'base_sales_amount_sum' => 'sum(base_sales_amount_sum)',
                // end
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

    /* Overrides the logic of
     * @see Mage_Core_Model_Mysql4_Collection_Abstract::_initSelect()
     * by joining the FIPS tables and grouping by "special" code
     */
    protected  function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()
                ->joinLeft(
                    array('pf' => 'unl_tax_places'),
                	'CASE ' . implode(' ', $this->_cityFipsCases) . ' ELSE NULL END',
                    array('city' => 'name')
                )
                ->joinLeft(
                    array('cf' => 'unl_tax_counties'),
                    "CASE WHEN code LIKE '%-CountyFips-%' THEN RIGHT(code, 3) = cf.county_id ELSE NULL END",
                    array('county' => 'name')
                )
                ->group(array($this->_periodFormat,
                	'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END'),
                    'percent'
                );
        }

        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }

        return $this;
    }
}