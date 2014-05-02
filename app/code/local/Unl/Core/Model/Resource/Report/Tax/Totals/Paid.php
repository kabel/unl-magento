<?php

class Unl_Core_Model_Resource_Report_Tax_Totals_Paid extends Mage_Sales_Model_Resource_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_periodExpr;

    protected $_selectedColumns    = array();

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('tax/sales_order_tax', 'tax_id');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _applyDateRangeFilter()
    {
        if ($this->_from !== null) {
            $this->getSelect()->where($this->getConnection()->getDatePartSql($this->_periodExpr) . ' >= ?', $this->_from);
        }
        if ($this->_to !== null) {
            $this->getSelect()->where($this->getConnection()->getDatePartSql($this->_periodExpr) . ' <= ?', $this->_to);
        }

        return $this;
    }

    protected function _getSelectedColumns()
    {
        /* @var $altResource Mage_Tax_Model_Resource_Report_Tax */
        $altResource = Mage::getResourceModel('tax/report_tax');

        $this->_periodExpr = $altResource->getStoreTZOffsetQuery(
            array('i' => $this->_getInnerMainTable()),
            'i.' . $this->_getInnerDateColumn(),
            $this->_from, $this->_to
        );

        if ('month' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_periodExpr, '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_periodExpr, '%Y');
        } else {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_periodExpr, '%Y-%m-%d');
        }

        $this->setAggregatedColumns(array(
            'orders_count'          => 'COUNT(DISTINCT o.entity_id)',
            'tax_base_amount_sum'   => 'SUM(t.base_real_amount * o.base_to_global_rate)',
        ));

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $codeCases = Mage::helper('unl_core')->getTaxCodeCases();
            $this->_selectedColumns = array(
                'period'                => $this->_periodFormat,
                'code'                  => $this->getConnection()->getCaseSql('', $codeCases, 'code'),
                'city'                  => 'pf.name',
                'county'                => 'cf.name',
                'percent'               => 't.percent',
                'base_sales_amount_sum' => 'SUM(t.base_sale_amount * o.base_to_global_rate)'
            ) + $this->getAggregatedColumns();
        }


        if ($this->isTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns();
        }

        if ($this->isSubTotals()) {
            $this->_selectedColumns = $this->getAggregatedColumns() + array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    protected function _getInnerMainTable()
    {
        return $this->getTable('sales/invoice');
    }

    protected function _getInnerDateColumn()
    {
        return 'paid_at';
    }

    protected function _getInnerSelect()
    {
        $col = $this->_getInnerDateColumn();
        $innerSelect = $this->getConnection()->select()
            ->from($this->_getInnerMainTable(),
                array($col => sprintf('MIN(%s)', $col), 'order_id'))
            ->where('base_tax_amount > 0')
            ->group('order_id');

        return $innerSelect;
    }

    protected function _initSelect()
    {
        $codeCases = Mage::helper('unl_core')->getTaxCodeCases();
        $cityFipsCases = Mage::helper('unl_core')->getCityFipsCases();
        $countyFipsCases = Mage::helper('unl_core')->getCountyFipsCases();
        $innerSelect = $this->_getInnerSelect();

        $this->getSelect()->from(array('t' => $this->getResource()->getMainTable()), $this->_getSelectedColumns())
            ->join(array('o' => $this->getTable('sales/order')), 'o.entity_id = t.order_id', array())
            ->join(array('i' => $innerSelect), 'i.order_id = o.entity_id', array())
            ->joinLeft(array('pf' => $this->getTable('unl_core/tax_places')),
                $this->getConnection()->getCaseSql('', $cityFipsCases), array())
            ->joinLeft(array('cf' => $this->getTable('unl_core/tax_counties')),
                $this->getConnection()->getCaseSql('', $countyFipsCases), array());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()->group(array(
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
