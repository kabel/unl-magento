<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Paid extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/invoice', 'entity_id');
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
            $this->_periodColumn = "CONVERT_TZ(i.paid_at, '+00:00', '{$this->_getStoreTimezoneUtcOffset()}')";
        }

        return $this->_periodColumn;
    }

    protected function _initSelectForProducts($groupOrder = false)
    {
        $this->_initSelectForShipping($groupOrder);

        $this->getSelect()
            ->join(array('ii' => $this->getTable('sales/invoice_item')), 'i.entity_id = ii.parent_id', array())
            ->join(array('oi' => $this->getTable('sales/order_item')), 'ii.order_item_id = oi.item_id AND oi.parent_item_id IS NULL', array());

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
            ->from(array('i' => $this->getResource()->getMainTable()) , $this->_getSelectedColumns())
            ->join(array('p' => $this->getTable('sales/order_payment')), 'i.order_id = p.parent_id', array())
            ->where('p.method IN (?)', $this->_paymentMethodCodes);

        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);

            if (!$this->isSubTotals() && $groupOrder) {
                $this->getSelect()
                    ->join(array('o' => $this->getTable('sales/order')), 'i.order_id = o.entity_id', array())
                    ->group('o.entity_id');
            }
        } else {
            $this->getSelect()->having('COUNT(*) > 0');
        }

        return $this;
    }
}