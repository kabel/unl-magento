<?php

class Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract extends Mage_Sales_Model_Resource_Report_Collection_Abstract
{
    protected $_periodColumn;
    protected $_periodExpr;
    protected $_periodFormat;
    protected $_mainItemTable;
    protected $_selectedColumns = array();
    protected $_paymentMethodCodes = array();

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
    }

    protected function _applyStoresFilter()
    {
        return $this;
    }

    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where($this->getConnection()->getDatePartSql($this->_periodColumn) . ' >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where($this->getConnection()->getDatePartSql($this->_periodColumn) . ' <= ?', $this->_to);
        }
        return $this;
    }


    protected function _getSelectedColumns()
    {
        $this->_setPeriodFormat();
        if (!$this->isTotals()) {
            $this->_selectedColumns = array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    protected function _setPeriodFormat()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_getPeriodExpr(), '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_getPeriodExpr(), '%Y');
        } else {
            $this->_periodFormat = $this->getConnection()->getDateFormatSql($this->_getPeriodExpr(), '%Y-%m-%d');
        }

        return $this;
    }

    protected function _getPeriodExpr()
    {
        if (null === $this->_periodExpr) {
            $this->_periodExpr = $this->getStoreTZOffsetQuery(
                array('e' => $this->getResource()->getMainTable()),
                "e.{$this->_periodColumn}",
                $this->_from, $this->_to
            );
        }

        return $this->_periodExpr;
    }

    protected function _getMainItemTable()
    {
        return $this->_mainItemTable;
    }

    protected function _initSelectForProducts($groupOrder = false)
    {
        $this->_initSelectForShipping($groupOrder);

        $this->getSelect()
            ->join(array('ei' => $this->_getMainItemTable()), 'ei.parent_id = e.entity_id AND ei.is_dummy = 0', array())
            ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.item_id = ei.order_item_id', array());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()
                ->joinLeft(array('s' => $this->getTable('core/store')), 's.store_id = oi.source_store_view', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 'sg.group_id = s.group_id', array('merchant' => 'name'))
                ->group('sg.group_id');
        }

        return $this;
    }

    protected function _initSelectForShipping($groupOrder = false)
    {
        $this->getSelect()
            ->from(array('e' => $this->getResource()->getMainTable()), $this->_getSelectedColumns())
            ->join(array('p' => $this->getTable('sales/order_payment')), 'e.order_id = p.parent_id', array())
            ->where('p.method IN (?)', $this->_paymentMethodCodes);

        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);

            if (!$this->isSubTotals() && $groupOrder) {
                $this->getSelect()
                    ->join(array('o' => $this->getTable('sales/order')), 'e.order_id = o.entity_id', array())
                    ->group('o.entity_id');
            }
        } else {
            $this->getSelect()->having('COUNT(*) > 0');
        }

        $this->_useAnalyticFunction = true;

        return $this;
    }

    public function getStoreTZOffsetQuery($table, $column, $from = null, $to = null, $store = null)
    {
        /* @var $resource Mage_Sales_Model_Resource_Report_Order */
        $resource = Mage::getResourceModel('sales/report_order');
        return $resource->getStoreTZOffsetQuery($table, $column, $from, $to, $store);
    }
}
