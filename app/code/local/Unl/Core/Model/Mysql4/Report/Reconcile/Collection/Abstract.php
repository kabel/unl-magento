<?php

class Unl_Core_Model_Mysql4_Report_Reconcile_Collection_Abstract extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_inited = false;
    protected $_selectedColumns = array(
        'total_qty_ordered'              => 'SUM(oi.qty_ordered - IFNULL(oi.qty_canceled, 0))',
        'total_qty_invoiced'             => 'SUM(oi.qty_invoiced)',
        'total_income_amount'            => 'SUM((oi.base_row_total + oi.base_tax_amount - ABS(oi.base_discount_amount) - ((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
        'total_revenue_amount'           => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
        'total_profit_amount'            => 'SUM((oi.base_row_invoiced - ABS(oi.base_discount_invoiced) - (((oi.base_row_invoiced - ABS(oi.base_discount_invoiced)) / oi.qty_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered, 0)) * oi.qty_refunded) - (IFNULL(oi.base_cost, 0) * oi.qty_invoiced)) * o.base_to_global_rate)',
        'total_invoiced_amount'          => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
        'total_canceled_amount'          => 'SUM((oi.base_row_total + oi.base_tax_amount - ABS(oi.base_discount_amount)) / oi.qty_ordered * oi.qty_canceled * o.base_to_global_rate)',
        'total_paid_amount'              => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
        'total_refunded_amount'          => 'SUM((((oi.base_row_invoiced - ABS(oi.base_discount_invoiced)) / oi.qty_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered, 0)) * oi.qty_refunded) * o.base_to_global_rate)',
        'total_tax_amount'               => 'SUM((oi.base_tax_amount - (oi.base_tax_amount / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
        'total_tax_amount_actual'        => 'SUM((IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - IFNULL(IF(oi.base_row_invoiced, oi.base_tax_amount, 0) / oi.qty_invoiced * oi.qty_refunded, 0)) * o.base_to_global_rate)',
        'total_discount_amount'          => 'SUM((ABS(oi.base_discount_amount) - (ABS(oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
        'total_discount_amount_actual'   => 'SUM((ABS(oi.base_discount_invoiced) - (ABS(oi.base_discount_invoiced) / oi.qty_invoiced * oi.qty_refunded)) * o.base_to_global_rate)',
    );
    protected $_recordType;
    protected $_paymentMethodCodes = array();

    /**
     * Initialize custom resource model
     *
     * @param array $parameters
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/order', 'entity_id');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Apply stores filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Order_Updatedat_Collection
     */
    protected function _applyStoresFilter()
    {
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
            $this->getSelect()->where('oi.source_store_view IN(?) OR oi.source_store_view IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()->where('oi.source_store_view IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Apply order status filter
     *
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    protected function _applyOrderStatusFilter($select = null)
    {
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
        $this->getSelect()->where('status IN(?)', $orderStatus);
        return $this;
    }

    /**
     * Retrieve array of columns to select
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        if (!$this->isTotals()) {
            if ('month' == $this->_period) {
                $this->_periodFormat = "DATE_FORMAT(DATE(CONVERT_TZ(o.{$this->getRecordType()}, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "')), '%Y-%m')";
            } elseif ('year' == $this->_period) {
                $this->_periodFormat = "EXTRACT(YEAR FROM DATE(CONVERT_TZ(o.{$this->getRecordType()}, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "')))";
            } else {
                $this->_periodFormat = "DATE(CONVERT_TZ(o.{$this->getRecordType()}, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))";
            }
            $this->_selectedColumns += array('period' => $this->_periodFormat, 'increment_id' => 'o.increment_id',);
        }
        return $this->_selectedColumns;
    }

    /**
     * Add selected data
     *
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }

        $columns =  $this->_getSelectedColumns();

        $mainTable = $this->getResource()->getMainTable();

        $select = $this->getSelect()
            ->from(array('o' => $mainTable), $columns)
            ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = o.entity_id AND oi.parent_item_id IS NULL', array())
            ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
            ->where('p.method IN (?)', $this->_paymentMethodCodes)
            ->where('o.state NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_NEW,
                Mage_Sales_Model_Order::STATE_CANCELED
            ));

        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();

        if ($this->_to !== null) {
            $select->where("DATE(CONVERT_TZ(o.{$this->getRecordType()}, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "')) <= DATE(?)", $this->_to);
        }

        if ($this->_from !== null) {
            $select->where("DATE(CONVERT_TZ(o.{$this->getRecordType()}, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "')) >= DATE(?)", $this->_from);
        }

        if (!$this->isTotals()) {
            $select->group(array($this->_periodFormat, 'order_id'));
        }

        $this->_inited = true;
        return $this;
    }

    /**
     * Load
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     * @return Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
        $this->setApplyFilters(false);
        return parent::load($printQuery, $logQuery);
    }

    public function setRecordType($type = 'created_at')
    {
        $this->_recordType = $type;

        return $this;
    }

    public function getRecordType()
    {
        if (null === $this->_recordType) {
            $this->setRecordType();
        }

        return $this->_recordType;
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