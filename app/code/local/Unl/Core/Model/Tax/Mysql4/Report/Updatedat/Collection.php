<?php

class Unl_Core_Model_Tax_Mysql4_Report_Updatedat_Collection extends Mage_Tax_Model_Mysql4_Report_Updatedat_Collection
{
    protected $_selectedColumns = array(
        'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)'
    );

    // Duplicated from Unl_Core_Model_Tax_Mysql4_Report_Collection
    protected $_codeCases = array(
        "WHEN code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%' THEN CONCAT('US-NE-', RIGHT(code, 14))",
        "WHEN code LIKE '%-CityFips+-%' THEN CONCAT('US-NE-CityFips-', SUBSTRING(code, LOCATE('-CityFips+-', code) + 11))",
    );

    // Duplicated from Unl_Core_Model_Tax_Mysql4_Report_Collection
    protected $_cityFipsCases = array(
        "WHEN code LIKE '%-CityFips-%' THEN RIGHT(code, 5) = pf.fips_place_number",
        "WHEN code LIKE '%-CityFips+-%' THEN SUBSTRING(code, LOCATE('-CityFips+-', code) + 11, 5) = pf.fips_place_number",
    );

    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Report_Updatedat_Collection::_getSelectedColumns()
     * by changing/adding standard columns
     */
    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = 'DATE_FORMAT(e.updated_at, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = 'EXTRACT(YEAR FROM e.updated_at)';
        } else {
            $this->_periodFormat = 'DATE(e.updated_at)';
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'store_id'              => 'store_id',
                // changed/added columns
                'code'                  => 'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END',
                'base_sales_amount_sum' => 'SUM(tax.base_sale_amount * e.base_to_global_rate)',
                // end
                'order_status'          => 'e.status',
                'percent'               => 'tax.percent',
                'orders_count'          => 'COUNT(DISTINCT(e.entity_id))',
                'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)'
            );
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns += array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }


    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Report_Updatedat_Collection::_initSelect()
     * by joining the FIPS tables and grouping by "special" code
     */
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }

        $columns = $this->_getSelectedColumns();
        $mainTable = $this->getResource()->getMainTable();

        $select = $this->getSelect()
            ->from(array('e' => $mainTable), $columns)
            ->joinInner(array('tax'=> $this->getTable('tax/sales_order_tax')), 'e.entity_id = tax.order_id', array())
            // join the FIPS tables
            ->joinLeft(array('pf' => 'unl_tax_places'),
            	'CASE ' . implode(' ', $this->_cityFipsCases) . ' ELSE NULL END',
                array('city' => 'name')
            )
            ->joinLeft(array('cf' => 'unl_tax_counties'),
            	"CASE WHEN tax.code LIKE '%-CountyFips-%' THEN RIGHT(tax.code, 3) = cf.county_id ELSE NULL END",
                array('county' => 'name')
            );

        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();

        if ($this->_from !== null) {
            $select->where('DATE(e.updated_at) >= DATE(?)', $this->_from);
        }

        if ($this->_to !== null) {
            $select->where('DATE(e.updated_at) <= DATE(?)', $this->_to);
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat,
                // special code
                'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END',
                'percent'
            ));
        }

        if ($this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat
            ));
        }

        $this->_inited = true;
        return $this;
    }
}