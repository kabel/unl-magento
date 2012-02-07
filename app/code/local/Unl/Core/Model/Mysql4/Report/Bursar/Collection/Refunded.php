<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Refunded extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/creditmemo', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
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
            $this->_periodFormat = "DATE_FORMAT(DATE({$this->_getPeriodColumn()}), '%Y-%m')";
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = "EXTRACT(YEAR FROM DATE({$this->_getPeriodColumn()}))";
        } else {
            $this->_periodFormat = "DATE({$this->_getPeriodColumn()})";
        }

        return $this;
    }

    protected function _getPeriodColumn()
    {
        if (null === $this->_periodColumn) {
            $this->_periodColumn = "CONVERT_TZ(c.refunded_at, '+00:00', '{$this->_getStoreTimezoneUtcOffset()}')";
        }

        return $this->_periodColumn;
    }

    protected function _initSelectForProducts($groupOrder = false)
    {
        $this->_initSelectForShipping($groupOrder);

        $this->getSelect()
            ->join(array('ci' => $this->getTable('sales/creditmemo_item')), 'c.entity_id = ci.parent_id AND ci.is_dummy = 0', array())
            ->join(array('oi' => $this->getTable('sales/order_item')), 'ci.order_item_id = oi.item_id', array());

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->getSelect()
                ->joinLeft(array('s' => $this->getTable('core/store')), 'oi.source_store_view = s.store_id', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 's.group_id = sg.group_id', array('merchant' => 'name'))
                ->group('sg.group_id');
        }

        return $this;
    }

    protected function _initSelectForShipping($groupOrder = false)
    {
        $this->getSelect()
            ->from(array('c' => $this->getResource()->getMainTable()) , $this->_getSelectedColumns())
            ->join(array('p' => $this->getTable('sales/order_payment')), 'c.order_id = p.parent_id', array())
            ->where('p.method IN (?)', $this->_paymentMethodCodes);

        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);

            if (!$this->isSubTotals() && $groupOrder) {
                $this->getSelect()
                    ->join(array('o' => $this->getTable('sales/order')), 'c.order_id = o.entity_id', array())
                    ->group('o.entity_id');
            }
        } else {
            $this->getSelect()->having('COUNT(*) > 0');
        }

        return $this;
    }
}
