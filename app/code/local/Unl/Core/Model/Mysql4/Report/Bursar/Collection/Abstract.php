<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_inited = false;
    protected $_selectedColumns = array();
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

        if (null === $select) {
            $select = $this->getSelect();
        }
        $select->where('status IN(?)', $orderStatus);
        return $this;
    }

    protected function _getTotalColumns($isSubtotal = false)
    {
        $this->_selectedColumns = array(
            'orders_count'                   => 'COUNT(o.entity_id)',
            'total_qty_ordered'              => 'SUM(oi.total_qty_ordered)',
            'total_qty_invoiced'             => 'SUM(oi.total_qty_invoiced)',
            'total_income_amount'            => 'SUM((o.base_grand_total - IFNULL(o.base_total_canceled, 0)) * o.base_to_global_rate)',
            'total_revenue_amount'           => 'SUM((o.base_total_paid - IFNULL(o.base_total_refunded, 0)) * o.base_to_global_rate)',
            'total_profit_amount'            => 'SUM((o.base_total_paid - IFNULL(o.base_total_refunded, 0) - IFNULL(o.base_tax_invoiced, 0) - IFNULL(o.base_shipping_invoiced, 0) - IFNULL(o.base_total_invoiced_cost, 0)) * o.base_to_global_rate)',
            'total_invoiced_amount'          => 'SUM(o.base_total_invoiced * o.base_to_global_rate)',
            'total_canceled_amount'          => 'SUM(IFNULL(o.base_total_canceled, 0) * o.base_to_global_rate)',
            'total_paid_amount'              => 'SUM(o.base_total_paid * o.base_to_global_rate)',
            'total_refunded_amount'          => 'SUM(o.base_total_refunded * o.base_to_global_rate)',
            'total_tax_amount'               => 'SUM((o.base_tax_amount - IFNULL(o.base_tax_canceled, 0)) * o.base_to_global_rate)',
            'total_tax_amount_actual'        => 'SUM((o.base_tax_invoiced - IFNULL(o.base_tax_refunded, 0)) * o.base_to_global_rate)',
            'total_shipping_amount'          => 'SUM((o.base_shipping_amount - IFNULL(o.base_shipping_canceled, 0)) * o.base_to_global_rate)',
            'total_shipping_amount_actual'   => 'SUM((o.base_shipping_invoiced - IFNULL(o.base_shipping_refunded, 0)) * o.base_to_global_rate)',
            'total_discount_amount'          => 'SUM((ABS(o.base_discount_amount) - IFNULL(o.base_discount_canceled, 0)) * o.base_to_global_rate)',
            'total_discount_amount_actual'   => 'SUM((o.base_discount_invoiced - IFNULL(o.base_discount_refunded, 0)) * o.base_to_global_rate)',
            'total_refunded_tax_amount'      => 'SUM(IFNULL(o.base_tax_refunded, 0) * o.base_to_global_rate)',
            'total_canceled_tax_amount'      => 'SUM(IFNULL(o.base_tax_canceled, 0) * o.base_to_global_rate)'
        );

        if ($isSubtotal) {
            if ('month' == $this->_period) {
                $this->_periodFormat = "DATE_FORMAT(o.{$this->getRecordType()}, '%Y-%m')";
            } elseif ('year' == $this->_period) {
                $this->_periodFormat = "EXTRACT(YEAR FROM o.{$this->getRecordType()})";
            } else {
                $this->_periodFormat = "DATE(o.{$this->getRecordType()})";
            }
            $this->_selectedColumns += array('period' => $this->_periodFormat);
        }

        return $this->_selectedColumns;
    }

    protected function _getNonTotalColumns($fromItems = true)
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = "DATE_FORMAT(o.{$this->getRecordType()}, '%Y-%m')";
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = "EXTRACT(YEAR FROM o.{$this->getRecordType()})";
        } else {
            $this->_periodFormat = "DATE(o.{$this->getRecordType()})";
        }
        $columns = array('period' => $this->_periodFormat);

        if ($fromItems) {
            $columns += array(
                'merchant'                       => 'sg.name',
                'orders_count'                   => 'COUNT(DISTINCT(o.entity_id))',
                'total_qty_ordered'              => 'SUM(oi.qty_ordered - IFNULL(oi.qty_canceled, 0))',
                'total_qty_invoiced'             => 'SUM(oi.qty_invoiced)',
                'total_income_amount'            => 'SUM((oi.base_row_total + oi.base_tax_amount - ABS(oi.base_discount_amount) - ((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
                'total_revenue_amount'           => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
                'total_profit_amount'            => 'SUM((oi.base_row_invoiced - ABS(oi.base_discount_invoiced) - oi.base_amount_refunded - (IFNULL(oi.base_cost, 0) * oi.qty_invoiced)) * o.base_to_global_rate)',
                'total_invoiced_amount'          => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
                'total_canceled_amount'          => 'SUM((oi.base_price + ((oi.base_tax_amount - ABS(oi.base_discount_amount)) / oi.qty_ordered)) * oi.qty_canceled * o.base_to_global_rate)',
                'total_paid_amount'              => 'SUM((oi.base_row_invoiced + IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - ABS(oi.base_discount_invoiced)) * o.base_to_global_rate)',
                'total_refunded_amount'          => new Zend_Db_Expr('0'),
                'total_tax_amount'               => 'SUM((oi.base_tax_amount - (oi.base_tax_amount / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
                'total_tax_amount_actual'        => 'SUM((IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * qty_invoiced, 0) - IFNULL(IF(oi.base_row_invoiced, oi.base_tax_amount, 0) / oi.qty_invoiced * oi.qty_refunded, 0)) * o.base_to_global_rate)',
                'total_shipping_amount'          => new Zend_Db_Expr('0'),
                'total_shipping_amount_actual'   => new Zend_Db_Expr('0'),
                'total_discount_amount'          => 'SUM((ABS(oi.base_discount_amount) - (ABS(oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
                'total_discount_amount_actual'   => 'SUM((ABS(oi.base_discount_invoiced) - (ABS(oi.base_discount_invoiced) / oi.qty_invoiced * oi.qty_refunded)) * o.base_to_global_rate)',
                'total_refunded_tax_amount'      => new Zend_Db_Expr('0'),
                'total_canceled_tax_amount'      => 'SUM(oi.base_tax_amount / oi.qty_ordered * oi.qty_canceled * o.base_to_global_rate)'
            );
        } else {
            $columns += array(
                'merchant'                       => new Zend_Db_Expr("'CENTRALIZED ACCOUNT'"),
                'orders_count'                   => new Zend_Db_Expr('0'),
                'total_qty_ordered'              => new Zend_Db_Expr('0'),
                'total_qty_invoiced'             => new Zend_Db_Expr('0'),
                'total_income_amount'            => 'SUM((o.base_shipping_amount + o.base_shipping_tax_amount - IFNULL(o.base_shipping_canceled + o.base_shipping_tax_amount, 0)) * o.base_to_global_rate)',
                'total_revenue_amount'           => 'SUM((IFNULL(o.base_shipping_invoiced + o.base_shipping_tax_amount, 0) - IFNULL(o.base_shipping_refunded, 0) - IFNULL(o.base_tax_refunded, 0) - IFNULL(o.base_subtotal_refunded - o.base_discount_refunded, 0)) * o.base_to_global_rate)',
                'total_profit_amount'            => new Zend_Db_Expr('0'),
                'total_invoiced_amount'          => 'SUM(IFNULL(o.base_shipping_invoiced + o.base_shipping_tax_amount, 0) * o.base_to_global_rate)',
                'total_canceled_amount'          => 'SUM(IFNULL(o.base_shipping_canceled + o.base_shipping_tax_amount , 0) * o.base_to_global_rate)',
                'total_paid_amount'              => 'SUM(IFNULL(o.base_shipping_invoiced + o.base_shipping_tax_amount, 0) * o.base_to_global_rate)',
                'total_refunded_amount'          => 'SUM(o.base_total_refunded * o.base_to_global_rate)',
                'total_tax_amount'               => 'SUM((o.base_shipping_tax_amount - IF(o.base_shipping_canceled, o.base_shipping_tax_amount, 0)) * o.base_to_global_rate)',
                'total_tax_amount_actual'        => 'SUM((IF(o.base_shipping_invoiced, o.base_shipping_tax_amount, 0) - IF(o.base_shipping_refunded, o.base_shipping_tax_amount, 0)) * o.base_to_global_rate)',
                'total_shipping_amount'          => 'SUM((o.base_shipping_amount - IFNULL(o.base_shipping_canceled, 0)) * o.base_to_global_rate)',
                'total_shipping_amount_actual'   => 'SUM((o.base_shipping_invoiced - IFNULL(o.base_shipping_refunded, 0)) * o.base_to_global_rate)',
                'total_discount_amount'          => new Zend_Db_Expr('0'),
                'total_discount_amount_actual'   => new Zend_Db_Expr('0'),
                'total_refunded_tax_amount'      => 'SUM(IFNULL(o.base_tax_refunded, 0) * o.base_to_global_rate)',
                'total_canceled_tax_amount'      => "SUM(IF(o.base_shipping_canceled, o.base_shipping_tax_amount, 0) * o.base_to_global_rate)"
            );
        }

        return $columns;
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

        $mainTable = $this->getResource()->getMainTable();

        $select = $this->getSelect();

        if ($this->isTotals() || $this->isSubTotals()) {
            $selectOrderItem = $this->getConnection()->select()
                ->from($this->getTable('sales/order_item'), array(
                    'order_id'           => 'order_id',
                    'total_qty_ordered'  => 'SUM(qty_ordered - IFNULL(qty_canceled, 0))',
                    'total_qty_invoiced' => 'SUM(qty_invoiced)',
                ))
                ->group('order_id');

            $select->from(array('o' => $mainTable), $this->_getTotalColumns($this->isSubTotals()))
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ));

            $this->_applyOrderStatusFilter();

            if ($this->isSubTotals()) {
                $select->group($this->_periodFormat);
            }

            if ($this->_to !== null) {
                $select->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
            }

            if ($this->_from !== null) {
                $select->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
            }
        } else {
            $sql1 = clone $this->getSelect();
            $sql1->from(array('o' => $mainTable), $this->_getNonTotalColumns(true))
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = o.entity_id AND oi.parent_item_id IS NULL', array())
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->joinLeft(array('s' => $this->getTable('core/store')), 'oi.source_store_view = s.store_id', array())
                ->joinLeft(array('sg' => $this->getTable('core/store_group')), 's.group_id = sg.group_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ))
                ->group(array($this->_periodFormat, 'sg.group_id'));
            $this->_applyOrderStatusFilter($sql1);

            $sql2 = clone $this->getSelect();
            $sql2->from(array('o' => $mainTable), $this->_getNonTotalColumns(false))
                ->join(array('p' => $this->getTable('sales/order_payment')), 'p.parent_id = o.entity_id', array())
                ->where('p.method IN (?)', $this->_paymentMethodCodes)
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED
                ))
                ->group($this->_periodFormat);
            $this->_applyOrderStatusFilter($sql2);

            if ($this->_to !== null) {
                $sql1->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
                $sql2->where("DATE(o.{$this->getRecordType()}) <= DATE(?)", $this->_to);
            }

            if ($this->_from !== null) {
                $sql1->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
                $sql2->where("DATE(o.{$this->getRecordType()}) >= DATE(?)", $this->_from);
            }

            $select->union(array('(' . $sql1 . ')', '(' . $sql2 . ')'));
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
}