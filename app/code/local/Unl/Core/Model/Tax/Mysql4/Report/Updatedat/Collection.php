<?php

class Unl_Core_Model_Tax_Mysql4_Report_Updatedat_Collection extends Mage_Tax_Model_Mysql4_Report_Updatedat_Collection
{
    protected $_selectedColumns = array(
        'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)'
    );

    protected $_codeCases = array(
        "WHEN code LIKE '%-CountyFips-%' OR code LIKE '%-CityFips-%' THEN CONCAT('US-NE-', RIGHT(code, 14))",
        "WHEN code LIKE '%-CityFips+-%' THEN CONCAT('US-NE-CityFips-', SUBSTRING(code, LOCATE('-CityFips+-', code) + 11))",
    );

    protected $_cityFipsCases = array(
        "WHEN code LIKE '%-CityFips-%' THEN RIGHT(code, 5) = pf.fips_place_number",
        "WHEN code LIKE '%-CityFips+-%' THEN SUBSTRING(code, LOCATE('-CityFips+-', code) + 11, 5) = pf.fips_place_number",
    );

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
                'code'                  => 'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END',
                'order_status'          => 'e.status',
                'percent'               => 'tax.percent',
                'orders_count'          => 'COUNT(DISTINCT(e.entity_id))',
                'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)',
                'base_sales_amount_sum' => 'SUM(tax.base_sale_amount * e.base_to_global_rate)'
            );
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns += array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Mage_Tax_Model_Mysql4_Report_Updatedat_Collection
     */
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }

        $columns = $this->_getSelectedColumns();
        $mainTable = $this->getResource()->getMainTable();

        if (!is_null($this->_from) || !is_null($this->_to)) {
            $where = (!is_null($this->_from)) ? "so.updated_at >= '{$this->_from}'" : '';
            if (!is_null($this->_to)) {
                $where .= (!empty($where)) ? " AND so.updated_at <= '{$this->_to}'" : "so.updated_at <= '{$this->_to}'";
            }

            $subQuery = clone $this->getSelect();
            $subQuery->from(array('so' => $mainTable), array('DISTINCT DATE(so.updated_at)'))
                ->where($where);
        }

        $select = $this->getSelect()
            ->from(array('e' => $mainTable), $columns)
            ->joinInner(array('tax'=> $this->getTable('tax/sales_order_tax')), 'e.entity_id = tax.order_id', array())
            ->joinLeft(array('pf' => 'unl_tax_places'), 'CASE ' . implode(' ', $this->_cityFipsCases) . ' ELSE NULL END', array('city' => 'name'))
            ->joinLeft(array('cf' => 'unl_tax_counties'), "CASE WHEN tax.code LIKE '%-CountyFips-%' THEN RIGHT(tax.code, 3) = cf.county_id ELSE NULL END", array('county' => 'name'));

        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();

        if (!is_null($this->_from) || !is_null($this->_to)) {
            $select->where("DATE(e.updated_at) IN(?)", new Zend_Db_Expr($subQuery));
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat,
                'store_id',
                'CASE ' . implode(' ', $this->_codeCases) . ' ELSE code END'
            ));
        }

        if ($this->isSubTotals()) {
            $select->group(array(
                $this->_periodFormat,
                'store_id'
            ));
        }

        $this->_inited = true;
        return $this;
    }
}