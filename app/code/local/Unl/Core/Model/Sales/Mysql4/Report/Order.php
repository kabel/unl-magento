<?php

class Unl_Core_Model_Sales_Mysql4_Report_Order extends Mage_Sales_Model_Mysql4_Report_Order
{
    /**
     * Aggregate Orders data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Unl_Core_Model_Sales_Mysql4_Report_Order
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $this->_getWriteAdapter()->beginTransaction();
        
        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales/order'),
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);

            $columns = array(
                'period'                         => "DATE(CONVERT_TZ(o.created_at, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))",
                'store_id'                       => 'oi.source_store_view',
                'order_status'                   => 'o.status',
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
                'total_tax_amount_actual'        => 'SUM((IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * oi.qty_invoiced, 0) - IFNULL(IF(oi.base_row_invoiced, oi.base_tax_amount / oi.qty_ordered * oi.qty_invoiced, 0) / oi.qty_invoiced * oi.qty_refunded, 0)) * o.base_to_global_rate)',
                'total_shipping_amount'          => new Zend_Db_Expr('0'),
                'total_shipping_amount_actual'   => new Zend_Db_Expr('0'),
                'total_discount_amount'          => 'SUM((ABS(oi.base_discount_amount) - (ABS(oi.base_discount_amount) / oi.qty_ordered * oi.qty_canceled)) * o.base_to_global_rate)',
                'total_discount_amount_actual'   => 'SUM((ABS(oi.base_discount_invoiced) - (ABS(oi.base_discount_invoiced) / oi.qty_invoiced * oi.qty_refunded)) * o.base_to_global_rate)'
            );
            
            $select = $this->_getWriteAdapter()->select();

            $select->from(array('o' => $this->getTable('sales/order')), $columns)
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = o.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED,
                ));

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'o.created_at'));
            }

            $select->group(array(
                'period',
                'store_id',
                'order_status'
            ));

            $this->_getWriteAdapter()->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));
            
            //Centralized Account
            $columns = array(
                'period'                         => "DATE(CONVERT_TZ(o.created_at, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))",
                'store_id'                       => new Zend_Db_Expr('NULL'),
                'order_status'                   => 'o.status',
                'orders_count'                   => new Zend_Db_Expr('0'),
                'total_qty_ordered'              => new Zend_Db_Expr('0'),
                'total_qty_invoiced'             => new Zend_Db_Expr('0'),
                'total_income_amount'            => 'SUM((o.base_shipping_amount - o.base_shipping_canceled) * o.base_to_global_rate)',
                'total_revenue_amount'           => 'SUM((IFNULL(o.base_shipping_invoiced + o.base_shipping_tax_amount, 0) - IFNULL(o.base_shipping_refunded, 0) - IFNULL(o.base_subtotal_refunded, 0)) * o.base_to_global_rate)',
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
                'total_discount_amount_actual'   => new Zend_Db_Expr('0')
            );

            $select->reset();
            $select->from(array('o' => $this->getTable('sales/order')), $columns)
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = o.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_CANCELED,
                ));

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'o.created_at'));
            }

            $select->group(array(
                'period',
                'store_id',
                'order_status'
            ));

            // setup all columns to select SUM() except period, store_id and order_status
            foreach (array_keys($columns) as $k) {
                $columns[$k] = 'SUM(' . $k . ')';
            }
            $columns['period']         = 'period';
            $columns['store_id']       = new Zend_Db_Expr('0');
            $columns['order_status']   = 'order_status';
            
            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where("store_id <> 0 OR store_id IS NULL");

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'order_status'
            ));

            $this->_getWriteAdapter()->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE);
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        $this->_getWriteAdapter()->commit();
        return $this;
    }
}