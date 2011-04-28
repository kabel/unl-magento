<?php

class Unl_Core_Model_Reports_Mysql4_Order_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
    public function filterScope($storeIds)
    {
        $order_items = Mage::getModel('sales/order_item')->getCollection();
        $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('order_id'))
            ->where('source_store_view IN (?)', $storeIds)
            ->group('order_id');
        $this->getSelect()->joinInner(array('scope' => $select), 'main_table.entity_id = scope.order_id', array());
    }

   /**
     * Prepare report summary
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    public function prepareSummary($range, $customStart, $customEnd, $isFilter=0, $websiteScope=1, $storeIds=array())
    {
        $this->checkIsLive($range);
        if ($this->_isLive) {
            $this->_prepareSummaryLive($range, $customStart, $customEnd, $isFilter, $websiteScope, $storeIds);
        } else {
            $this->_prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter, $websiteScope, $storeIds);
        }

        return $this;
    }

    /**
     * Prepare report summary from live data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter=0, $websiteScope=1, $storeIds=array())
    {
        $this->setMainTable('sales/order');
        if ($isFilter==0) {
            $this->getSelect()->columns(array(
                'revenue' => 'SUM(main_table.base_grand_total*main_table.base_to_global_rate)',
                'quantity' => 'COUNT(main_table.entity_id)',
                'range' => $this->_getRangeExpressionForAttribute($range, 'created_at'),
            ));
            $this->addFieldToFilter('created_at', $this->getDateRange($range, $customStart, $customEnd));
        } else{
            if ($websiteScope) {
                $this->getSelect()->columns(array(
                    'revenue' => 'SUM(main_table.base_grand_total)',
                    'quantity' => 'COUNT(main_table.entity_id)',
                    'range' => $this->_getRangeExpressionForAttribute($range, 'created_at'),
                ));

                $this->addFieldToFilter('store_id', array('in' => $storeIds));
                $this->addFieldToFilter('created_at', $this->getDateRange($range, $customStart, $customEnd));
            } else {
                $this->getSelect()->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id AND oi.parent_item_id IS NULL', array());

                $this->getSelect()->columns(array(
                    'revenue' => 'SUM(oi.base_row_total - ABS(oi.base_discount_amount) - ((oi.base_row_total - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled))',
                    'quantity' => 'COUNT(DISTINCT(main_table.entity_id))',
                    'range' => $this->_getRangeExpressionForAttribute($range, 'main_table.created_at'),
                ));

                $this->addFieldToFilter('oi.source_store_view', array('in' => $storeIds));
                $this->addFieldToFilter('main_table.created_at', $this->getDateRange($range, $customStart, $customEnd));
            }
        }

        $this->getSelect()->order('range', 'asc')
            ->group('range');

        $this->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        return $this;
    }

    /**
     * Prepare report summary from aggregated data
     *
     * @param string $range
     * @param mixed $customStart
     * @param mixed $customEnd
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    protected function _prepareSummaryAggregated($range, $customStart, $customEnd, $isFilter=0, $websiteScope=1, $storeIds=array())
    {
        $this->setMainTable('sales/order_aggregated_created');
        $this->getSelect()->columns(array(
            'revenue' => 'SUM(main_table.total_revenue_amount)',
            'quantity' => 'SUM(main_table.orders_count)',
            'range' => $this->_getRangeExpressionForAttribute($range, 'main_table.period'),
        ))->order('range', 'asc')
        ->group('range');

        $this->getSelect()->where(
            $this->_getConditionSql('main_table.period', $this->getDateRange($range, $customStart, $customEnd))
        );

        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }

        if (empty($storeIds)) {
            $storeIds = array(Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId());
        }
        $this->addFieldToFilter('store_id', array('in' => $storeIds));

        $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        return $this;
    }

    /**
     * Calculate lifitime sales
     *
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    public function calculateSales($isFilter = 0, $websiteScope = 1, $storeIds = array())
    {
        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data')) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();

            $this->getSelect()->columns(array(
                'lifetime' => 'SUM(main_table.total_revenue_amount)',
                'average'  => "IF(SUM(main_table.orders_count) > 0, SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count), 0)"
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );
            } else {
                $this->addFieldToFilter('store_id', array('in' => $storeIds));
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('sales/order');
            $this->removeAllFieldsFromSelect();

            if (!$isFilter || $websiteScope) {
                $expr = 'IFNULL(main_table.base_subtotal, 0) - IFNULL(main_table.base_subtotal_refunded, 0)'
                . ' - IFNULL(main_table.base_subtotal_canceled, 0) - ABS(IFNULL(main_table.base_discount_amount, 0))'
                . ' + IFNULL(main_table.base_discount_refunded, 0)';

                $this->getSelect()->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average'  => "AVG({$expr})"
                ));

                if ($isFilter) {
                    $collection->addFieldToFilter('store_id', array('in' => $storeIds));
                }
            } else {
                $this->getSelect()->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id AND oi.parent_item_id IS NULL', array());

                $expr = 'oi.base_row_total - ABS(oi.base_discount_amount) - ((oi.base_row_total - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)';

                $this->getSelect()->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average'  => "SUM({$expr})/COUNT(DISTINCT(main_table.entity_id))"
                ));

                $this->addFieldToFilter('oi.source_store_view', array('in' => $storeIds));
            }

            $this->getSelect()->where('main_table.status NOT IN(?)', $statuses)
                ->where('main_table.state NOT IN(?)', array(Mage_Sales_Model_Order::STATE_NEW, Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
        }
        return $this;
    }

    /**
     * Calculate totals report
     *
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    public function calculateTotals($isFilter = 0, $websiteScope = 1, $storeIds = array())
    {
        if ($this->isLive()) {
            $this->_calculateTotalsLive($isFilter, $websiteScope, $storeIds);
        } else {
            $this->_calculateTotalsAggregated($isFilter, $websiteScope, $storeIds);
        }

        return $this;
    }

    /**
     * Calculate totals live report
     *
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    protected function _calculateTotalsLive($isFilter = 0, $websiteScope = 1, $storeIds = array())
    {
        $this->setMainTable('sales/order');
        $this->removeAllFieldsFromSelect();

        if ($isFilter == 0) {
            $this->getSelect()->columns(array(
                'revenue' => 'SUM((main_table.base_subtotal-IFNULL(main_table.base_subtotal_refunded,0)-IFNULL(main_table.base_subtotal_canceled,0)-IFNULL(main_table.base_discount_amount,0)+IFNULL(main_table.base_discount_refunded,0))*main_table.base_to_global_rate)',
                'tax' => 'SUM((main_table.base_tax_amount-IFNULL(main_table.base_tax_refunded,0)-IFNULL(main_table.base_tax_canceled,0))*main_table.base_to_global_rate)',
                'shipping' => 'SUM((main_table.base_shipping_amount-IFNULL(main_table.base_shipping_refunded,0)-IFNULL(main_table.base_shipping_canceled,0))*main_table.base_to_global_rate)',
                'quantity' => 'COUNT(main_table.entity_id)'
            ));
        } else {
            if ($websiteScope) {
                $this->getSelect()->columns(array(
                    'revenue' => 'SUM((main_table.base_subtotal-IFNULL(main_table.base_subtotal_refunded,0)-IFNULL(main_table.base_subtotal_canceled,0)-IFNULL(main_table.base_discount_amount,0)+IFNULL(main_table.base_discount_refunded,0)))',
                    'tax' => 'SUM((main_table.base_tax_amount-IFNULL(main_table.base_tax_refunded,0)-IFNULL(main_table.base_tax_canceled,0)))',
                    'shipping' => 'SUM((main_table.base_shipping_amount-IFNULL(main_table.base_shipping_refunded,0)-IFNULL(main_table.base_shipping_canceled,0)))',
                    'quantity' => 'COUNT(main_table.entity_id)'
                ));

                $this->addFieldToFilter('store_id', array('in' => $storeIds));
            } else {
                $this->getSelect()->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id AND oi.parent_item_id IS NULL', array());

                $this->getSelect()->columns(array(
                    'revenue' => 'SUM(oi.base_row_total - ABS(oi.base_discount_amount) - ((oi.base_row_total - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled))',
                    'tax' => 'SUM(oi.base_tax_amount - (oi.base_tax_amount / oi.qty_ordered * oi.qty_canceled) - IFNULL(oi.base_tax_amount / oi.qty_ordered * oi.qty_refunded, 0))',
                    'shipping' => '0',
                    'quantity' => 'COUNT(DISTINCT(main_table.entity_id))'
                ));

                $this->addFieldToFilter('oi.source_store_view', array('in' => $storeIds));
            }
        }

        $this->addFieldToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));

        return $this;
    }

    /**
     * Calculate totals agregated report
     *
     * @param int $isFilter
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    protected function _calculateTotalsAggregated($isFilter = 0, $websiteScope = 1, $storeIds = array())
    {
        $this->setMainTable('sales/order_aggregated_created');
        $this->removeAllFieldsFromSelect();

        $this->getSelect()->columns(array(
            'revenue' => 'SUM(main_table.total_revenue_amount)',
            'tax' => 'SUM(main_table.total_tax_amount_actual)',
            'shipping' => 'SUM(main_table.total_shipping_amount_actual)',
            'quantity' => 'SUM(orders_count)',
        ));

        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }

        if (empty($storeIds)) {
            $storeIds = array(Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId());
        }
        $this->addFieldToFilter('store_id', array('in' => $storeIds));

        $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);

        return $this;
    }

    /**
     * Add period filter by created_at attribute
     *
     * @param string $period
     * @return Unl_Core_Model_Reports_Mysql4_Order_Collection
     */
    public function addCreateAtPeriodFilter($period)
    {
        list($from, $to) = $this->getDateRange($period, 0, 0, true);

        $this->checkIsLive($period);

        if ($this->isLive()) {
            $fieldToFilter = 'main_table.created_at';
        } else {
            $fieldToFilter = 'period';
        }

        $this->addFieldToFilter($fieldToFilter, array(
            'from'  => $from->toString(Varien_Date::DATETIME_INTERNAL_FORMAT),
            'to'    => $to->toString(Varien_Date::DATETIME_INTERNAL_FORMAT)
        ));

        return $this;
    }
}
