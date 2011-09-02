<?php

class Unl_Core_Model_Mysql4_Report_Customer_Orderaddress_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_periodColumn;

	/**
     * Initialize custom resource model
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/order_address', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    protected function _initSelect()
    {
        $_joinCondition = $this->getConnection()->quoteInto(
            'order.entity_id = main_table.parent_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
        );

        $this->_setPeriodFormat();
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()), array('*', 'period' => $this->_periodFormat))
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                $_joinCondition,
                array('ordernum' => 'increment_id')
            );

        $this->addFieldToFilter('address_type', 'billing');

        return $this;
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
            $this->_periodColumn = "CONVERT_TZ(order.created_at, '+00:00', '{$this->_getStoreTimezoneUtcOffset()}')";
        }

        return $this->_periodColumn;
    }

    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where("DATE({$this->_periodColumn})  >= ?", $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where("DATE({$this->_periodColumn}) <= ?", $this->_to);
        }
        return $this;
    }

    protected function _applyStoresFilter()
    {
        $select = Mage::helper('unl_core')->addAdminScopeFilters($this, 'parent_id');

        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->_joinScope($select, $storeIds, true);
        } elseif ($storeIds[0] != '') {
            $this->_joinScope($select, $storeIds);
        }

        return $this;
    }

    protected function _joinScope($select, $storeIds, $null = false)
    {
        if (!$select) {
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'))
                ->group('order_id');

            $this->getSelect()
                ->join(array('scope' => $select), 'main_table.parent_id = scope.order_id', array());
        }
        $select->where('source_store_view IN (?)' . ($null ? ' OR source_store_view IS NULL' : ''), $storeIds);

        return $this;
    }

	/**
     * Retrieve store timezone offset from UTC in the form acceptable by SQL's CONVERT_TZ()
     *
     * @return string
     */
    protected function _getStoreTimezoneUtcOffset($store = null)
    {
        return Mage::app()->getLocale()->storeDate($store)->toString(Zend_Date::GMT_DIFF_SEP);
    }
}
