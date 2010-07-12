<?php

class Unl_Core_Model_Sales_Mysql4_Order extends Mage_Sales_Model_Mysql4_Order
{
    /**
     * Aggregate Orders data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Mysql4_Order
     */
    public function aggregate($from = null, $to = null)
    {
        $writeAdapter = $this->getWriteConnection();
        try {
            if (!is_null($from)) {
                $from = $this->formatDate($from);
            }
            if (!is_null($to)) {
                $from = $this->formatDate($to);
            }

            $tableName = $this->getTable('sales/order_aggregated_created');

            $writeAdapter->beginTransaction();

            if (is_null($from) && is_null($to)) {
                $writeAdapter->query("TRUNCATE TABLE {$tableName}");
            } else {
                $where = (!is_null($from)) ? "so.updated_at >= '{$from}'" : '';
                if (!is_null($to)) {
                    $where .= (!empty($where)) ? " AND so.updated_at <= '{$to}'" : "so.updated_at <= '{$to}'";
                }

                $subQuery = $writeAdapter->select();
                $subQuery->from(array('so' => $this->getTable('sales/order')), array('DISTINCT DATE(so.created_at)'))
                    ->where($where);

                $deleteCondition = 'DATE(period) IN (' . new Zend_Db_Expr($subQuery) . ')';
                $writeAdapter->delete($tableName, $deleteCondition);
            }

            $columns = array(
                'period'                    => 'DATE(e.created_at)',
                'store_id'                  => 'oi.source_store_view',
                'order_status'              => 'e.status',
                'orders_count'              => 'COUNT(DISTINCT(e.entity_id))',
                'total_qty_ordered'         => 'SUM(oi.qty_ordered)',
                'base_profit_amount'        => 'SUM((oi.base_row_invoiced - oi.base_amount_refunded - (oi.base_cost * oi.qty_invoiced)) * e.base_to_global_rate)',
                'base_subtotal_amount'      => 'SUM(oi.base_row_total * e.base_to_global_rate)',
                'base_tax_amount'           => 'SUM(oi.base_tax_amount * e.base_to_global_rate)',
                'base_shipping_amount'      => new Zend_Db_Expr('0'),
                'base_discount_amount'      => 'SUM(oi.base_discount_amount * e.base_to_global_rate)',
                'base_grand_total_amount'   => 'SUM((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - oi.base_discount_invoiced) * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM(oi.base_amount_refunded * e.base_to_global_rate)',
                'base_canceled_amount'      => 'SUM((oi.base_price + (oi.base_tax_amount / oi.qty_ordered)) * oi.qty_canceled * e.base_to_global_rate)'
            );

            $select = $writeAdapter->select()
                ->from(array('e' => $this->getTable('sales/order')), $columns)
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(e.created_at) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");
            
            //Centralized Account
            $columns = array(
                'period'                    => 'DATE(e.created_at)',
                'store_id'                  => new Zend_Db_Expr('NULL'),
                'order_status'              => 'e.status',
                'orders_count'              => 'COUNT(DISTINCT(e.entity_id))',
                'total_qty_ordered'         => new Zend_Db_Expr('0'),
                'base_profit_amount'        => new Zend_Db_Expr('0'),
                'base_subtotal_amount'      => new Zend_Db_Expr('0'),
                'base_tax_amount'           => 'SUM(IFNULL(e.base_shipping_tax_amount, 0) * e.base_to_global_rate)',
                'base_shipping_amount'      => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
                'base_discount_amount'      => new Zend_Db_Expr('0'),
                'base_grand_total_amount'   => 'SUM((e.base_shipping_amount + IFNULL(e.base_shipping_tax_amount, 0)) * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM(IFNULL(e.base_shipping_invoiced + e.base_shipping_tax_amount, 0) * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM((IFNULL(e.base_subtotal_refunded, 0) - IFNULL(e.base_discount_refunded, 0) + IFNULL(e.base_tax_refunded, 0) + IFNULL(e.base_shipping_refunded, 0)) * e.base_to_global_rate)',
                'base_canceled_amount'      => 'SUM(IFNULL(e.base_shipping_canceled, 0))'
            );

            $select = $writeAdapter->select()
                ->from(array('e' => $this->getTable('sales/order')), $columns)
                ->where('e.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(e.created_at) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

            $select = $writeAdapter->select();
            $columns = array(
                'period'                    => 'period',
                'store_id'                  => new Zend_Db_Expr('0'),
                'order_status'              => 'order_status',
                'orders_count'              => new Zend_Db_Expr('0'),
                'total_qty_ordered'         => 'SUM(total_qty_ordered)',
                'base_profit_amount'        => 'SUM(base_profit_amount)',
                'base_subtotal_amount'      => 'SUM(base_subtotal_amount)',
                'base_tax_amount'           => 'SUM(base_tax_amount)',
                'base_shipping_amount'      => 'SUM(base_shipping_amount)',
                'base_discount_amount'      => 'SUM(base_discount_amount)',
                'base_grand_total_amount'   => 'SUM(base_grand_total_amount)',
                'base_invoiced_amount'      => 'SUM(base_invoiced_amount)',
                'base_refunded_amount'      => 'SUM(base_refunded_amount)',
                'base_canceled_amount'      => 'SUM(base_canceled_amount)'
            );
            $select->from($tableName, $columns)
                ->where("store_id <> 0 OR store_id IS NULL");

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(period) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(array(
                    'period',
                    'order_status'
                ));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

            $reportsFlagModel = Mage::getModel('reports/flag');
            $reportsFlagModel->setReportFlagCode(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE);
            $reportsFlagModel->loadSelf();
            $reportsFlagModel->save();

        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        $writeAdapter->commit();
        return $this;
    }
}