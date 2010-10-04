<?php

class Unl_Core_Model_Sales_Mysql4_Report_Order_Updatedat_Collection extends Mage_Sales_Model_Mysql4_Report_Order_Updatedat_Collection
{
    protected $_selectedColumns = array(
        'total_qty_ordered'              => 'IFNULL(SUM(oi.total_qty_ordered), 0)',
        'total_qty_invoiced'             => 'IFNULL(SUM(oi.total_qty_invoiced), 0)',
        'total_income_amount'            => 'IFNULL(SUM((e.base_grand_total - IFNULL(e.base_total_canceled, 0)) * e.base_to_global_rate), 0)',
        'total_revenue_amount'           => 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0)) * e.base_to_global_rate), 0)',
        'total_profit_amount'            => 'IFNULL(SUM((e.base_total_paid - IFNULL(e.base_total_refunded, 0) - IFNULL(e.base_tax_invoiced, 0) - IFNULL(e.base_shipping_invoiced, 0) - IFNULL(e.base_total_invoiced_cost, 0)) * e.base_to_global_rate), 0)',
        'total_invoiced_amount'          => 'IFNULL(SUM(e.base_total_invoiced * e.base_to_global_rate), 0)',
        'total_canceled_amount'          => 'IFNULL(SUM(e.base_total_canceled * e.base_to_global_rate), 0)',
        'total_paid_amount'              => 'IFNULL(SUM(e.base_total_paid * e.base_to_global_rate), 0)',
        'total_refunded_amount'          => 'IFNULL(SUM(e.base_total_refunded * e.base_to_global_rate), 0)',
        'total_tax_amount'               => 'IFNULL(SUM((e.base_tax_amount - IFNULL(e.base_tax_canceled, 0)) * e.base_to_global_rate), 0)',
        'total_tax_amount_actual'        => 'IFNULL(SUM((e.base_tax_invoiced - IFNULL(e.base_tax_refunded, 0)) * e.base_to_global_rate), 0)',
        'total_shipping_amount'          => 'IFNULL(SUM((e.base_shipping_amount - IFNULL(e.base_shipping_canceled, 0)) * e.base_to_global_rate), 0)',
        'total_shipping_amount_actual'   => 'IFNULL(SUM((e.base_shipping_invoiced - IFNULL(e.base_shipping_refunded, 0)) * e.base_to_global_rate), 0)',
        'total_discount_amount'          => 'IFNULL(SUM((ABS(e.base_discount_amount) - IFNULL(e.base_discount_canceled, 0)) * e.base_to_global_rate), 0)',
        'total_discount_amount_actual'   => 'IFNULL(SUM((e.base_discount_invoiced - IFNULL(e.base_discount_refunded, 0)) * e.base_to_global_rate), 0)',
    );
    
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
            $this->getSelect()
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('oi.source_store_view IN(?) OR oi.source_store_view IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('oi.source_store_view IN(?)', $storeIds);
        } else {
            $selectOrderItem = $this->getConnection()->select()
                ->from($this->getTable('sales/order_item'), array(
                    'order_id'           => 'order_id',
                    'total_qty_ordered'  => 'SUM(qty_ordered - IFNULL(qty_canceled, 0))',
                    'total_qty_invoiced' => 'SUM(qty_invoiced)',
                ))
                ->group('order_id');
                
            $this->getSelect()->join(array('oi' => $selectOrderItem), 'oi.order_id = e.entity_id', array());
        }

        return $this;
    }
    
    /**
     * Retrieve array of columns to select
     *
     * @return array
     */
    protected function _getSelectedColumns()
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

        if ($nullCheck || $storeIds[0] != '') {
            $this->_selectedColumns = array(
                'total_qty_ordered'              => 'SUM(oi.qty_ordered - IFNULL(oi.qty_canceled, 0))',
                'total_qty_invoiced'             => 'SUM(oi.qty_invoiced)',
                'total_income_amount'            => 'SUM((oi.base_row_total + oi.base_tax_amount - ABS(oi.base_discount_amount) - ((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * e.base_to_global_rate)',
                'total_revenue_amount'           => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - ABS(oi.base_discount_invoiced)) * e.base_to_global_rate)',
                'total_profit_amount'            => 'SUM((oi.base_row_invoiced - ABS(oi.base_discount_invoiced) - oi.base_amount_refunded - (IFNULL(oi.base_cost, 0) * oi.qty_invoiced)) * e.base_to_global_rate)',
                'total_invoiced_amount'          => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - ABS(oi.base_discount_invoiced)) * e.base_to_global_rate)',
                'total_canceled_amount'          => 'SUM((oi.base_price + ((oi.base_tax_amount - ABS(oi.base_discount_amount)) / oi.qty_ordered)) * oi.qty_canceled * e.base_to_global_rate)',
                'total_paid_amount'              => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - ABS(oi.base_discount_invoiced)) * e.base_to_global_rate)',
                'total_refunded_amount'          => new Zend_Db_Expr('0'),
                'total_tax_amount'               => 'SUM((oi.base_tax_amount - (oi.base_tax_amount / oi.qty_ordered * oi.qty_canceled)) * e.base_to_global_rate)',
                'total_tax_amount_actual'        => 'SUM((oi.base_tax_invoiced - (oi.base_tax_invoiced / oi.qty_invoiced * oi.qty_refunded)) * e.base_to_global_rate)',
                'total_shipping_amount'          => new Zend_Db_Expr('0'),
                'total_shipping_amount_actual'   => new Zend_Db_Expr('0'),
                'total_discount_amount'          => 'SUM((ABS(oi.base_discount_amount) - (ABS(oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * e.base_to_global_rate)',
                'total_discount_amount_actual'   => 'SUM((ABS(oi.base_discount_invoiced) - (ABS(oi.base_discount_invoiced) / oi.qty_invoiced * oi.qty_refunded)) * e.base_to_global_rate)'
            );
        }
        
        if (!$this->isTotals()) {
            if ('month' == $this->_period) {
                $this->_periodFormat = 'DATE_FORMAT(e.updated_at, \'%Y-%m\')';
            } elseif ('year' == $this->_period) {
                $this->_periodFormat = 'EXTRACT(YEAR FROM e.updated_at)';
            } else {
                $this->_periodFormat = 'DATE(e.updated_at)';
            }
            $this->_selectedColumns += array(
                'period'       => $this->_periodFormat,
                'orders_count' => 'COUNT(DISTINCT(e.entity_id))'
            );
        }
        return $this->_selectedColumns;
    }
    
    protected function _initSelect()
    {
        if ($this->_inited) {
            return $this;
        }
        
        $columns = $this->_getSelectedColumns();

        $mainTable = $this->getResource()->getMainTable();

        $select = $this->getSelect()
            ->from(array('e' => $mainTable), $columns)
            ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED,
                ));

        $this->_applyStoresFilter();
        $this->_applyOrderStatusFilter();

        if ($this->_to !== null) {
            $select->where('DATE(e.updated_at) <= DATE(?)', $this->_to);
        }

        if ($this->_from !== null) {
            $select->where('DATE(e.updated_at) >= DATE(?)', $this->_from);
        }

        if (!$this->isTotals()) {
            $select->group($this->_periodFormat);
        }

        $this->_inited = true;
        return $this;
    }
}