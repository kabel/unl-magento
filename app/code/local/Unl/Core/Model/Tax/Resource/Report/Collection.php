<?php

class  Unl_Core_Model_Tax_Resource_Report_Collection extends Mage_Tax_Model_Resource_Report_Collection
{
    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Report_Collection::_getSelectedColumns()
     * by changing/adding standard columns
     */
    protected function _getSelectedColumns()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y');
        } else {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql('period', '%Y-%m-%d');
        }

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $codeCases = Mage::helper('unl_core')->getTaxCodeCases();
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                // changed/new columns
                'code'                  => $this->getConnection()->getCaseSql('', $codeCases, 'code'),
                'base_sales_amount_sum' => 'SUM(base_sales_amount_sum)',
                // end
                'percent'               => 'percent',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)'
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
    protected function _initSelect()
    {
        $this->getSelect()->from($this->getResource()->getMainTable() , $this->_getSelectedColumns());
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $codeCases = Mage::helper('unl_core')->getTaxCodeCases();
            $cityFipsCases = Mage::helper('unl_core')->getCityFipsCases();
            $countyFipsCases = Mage::helper('unl_core')->getCountyFipsCases();

            $this->getSelect()
                ->joinLeft(
                    array('pf' => 'unl_tax_places'),
                    $this->getConnection()->getCaseSql('', $cityFipsCases),
                    array('city' => 'name')
                )
                ->joinLeft(
                    array('cf' => 'unl_tax_counties'),
                    $this->getConnection()->getCaseSql('', $countyFipsCases),
                    array('county' => 'name')
                )
                ->group(array(
                    $this->_periodFormat,
                	$this->getConnection()->getCaseSql('', $codeCases, 'code'),
                    'percent'
                ));
        }

        if ($this->isSubTotals()) {
            $this->getSelect()->group(array(
                $this->_periodFormat
            ));
        }

        /**
         * Allow to use analytic function
         */
        $this->_useAnalyticFunction = true;

        return $this;
    }
}
