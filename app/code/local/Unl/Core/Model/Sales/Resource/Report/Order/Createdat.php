<?php

class Unl_Core_Model_Sales_Resource_Report_Order_Createdat extends Mage_Sales_Model_Resource_Report_Order_Createdat
{
    /* Overrides
     * @see Mage_Sales_Model_Resource_Report_Order_Createdat::_aggregateByField()
     * by using order items for totals and using source store
     */
    protected function _aggregateByField($aggregationField, $from, $to)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to   = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $adapter = $this->_getWriteAdapter();

        $adapter->beginTransaction();
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

            $periodExpr = $adapter->getDatePartSql($this->getStoreTZOffsetQuery(
                array('o' => $this->getTable('sales/order')),
                'o.' . $aggregationField,
                $from, $to
            ));
            // Columns list
            $columns = array(
                // convert dates from UTC to current admin timezone
                'period'                         => $periodExpr,
                'store_id'                       => 'oi.source_store_view', // use source_store instead
                'order_status'                   => 'o.status',
                'orders_count'                   => new Zend_Db_Expr('COUNT(DISTINCT o.entity_id)'),
                'total_qty_ordered'              => new Zend_Db_Expr('SUM(oi.qty_ordered - oi.qty_canceled)'),
                'total_qty_invoiced'             => new Zend_Db_Expr('SUM(oi.qty_invoiced)'),
                'total_income_amount'            => new Zend_Db_Expr(
                    sprintf('SUM((%s + %s - ABS(%s) - ((%s + %s - ABS(%s)) / oi.qty_ordered * oi.qty_canceled)) * %s)',
                        $adapter->getIfNullSql('oi.base_row_total', 0),
                        $adapter->getIfNullSql('oi.base_tax_amount', 0),
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_row_total', 0),
                        $adapter->getIfNullSql('oi.base_tax_amount', 0),
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_revenue_amount'           => new Zend_Db_Expr(
                    sprintf('SUM((%s - ABS(%s) - %s) * %s)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_profit_amount'            => new Zend_Db_Expr(
                    sprintf('SUM((%s) * %s)',
                        $adapter->getCheckSql('o.base_total_paid > 0',
                            sprintf('%s - ABS(%s) - %s - (%s * oi.qty_invoiced)',
                                $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                                $adapter->getIfNullSql('oi.base_discount_invoiced', 0),
                                $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                                $adapter->getIfNullSql('oi.base_cost', 0)
                            ),
                            '0'
                        ),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_invoiced_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((%s + %s - ABS(%s)) * %s)',
                        $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(((%s + %s - ABS(%s)) / oi.qty_ordered * oi.qty_canceled) * %s)',
                        $adapter->getIfNullSql('oi.base_row_total', 0),
                        $adapter->getIfNullSql('oi.base_tax_amount', 0),
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount'              => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getCheckSql('o.base_total_paid > 0',
                            sprintf('%s + %s - ABS(%s)',
                                $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                                $adapter->getIfNullSql('oi.base_tax_amount', 0),
                                $adapter->getIfNullSql('oi.base_discount_invoiced', 0)
                            ),
                            '0'
                        ),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('oi.base_amount_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount'               => new Zend_Db_Expr(
                    sprintf('SUM((%s - (%s / oi.qty_ordered * oi.qty_canceled)) * %s)',
                        $adapter->getIfNullSql('oi.base_tax_amount', 0),
                        $adapter->getIfNullSql('oi.base_tax_amount', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual'        => new Zend_Db_Expr(
                    sprintf('SUM((%s * %s) - (%s / %s * %s))',
                        $adapter->getIfNullSql('oi.base_tax_invoiced', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0),
                        $adapter->getIfNullSql('oi.tax_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_order_rate', 1),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount'          => new Zend_Db_Expr('0'),
                'total_shipping_amount_actual'   => new Zend_Db_Expr('0'),
                'total_discount_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((ABS(%s) - (ABS(%s) / oi.qty_ordered * oi.qty_canceled)) * %s)',
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount_actual'   => new Zend_Db_Expr(
                    sprintf('SUM((ABS(%s) - (ABS(%s) / oi.qty_ordered * oi.qty_refunded)) * %s)',
                        $adapter->getIfNullSql('oi.base_discount_invoiced', 0),
                        $adapter->getIfNullSql('oi.base_discount_amount', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                )
            );

            $select          = $adapter->select();
            $selectOrderItem = $this->getTable('sales/order_item');

            $select->from(array('o' => $this->getTable('sales/order')), $columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                ->where('oi.parent_item_id IS NULL')
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                $periodExpr,
                'oi.source_store_view',
                'o.status',
            ));

            $adapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            //Centralized Account
            $columns = array(
                // convert dates from UTC to current admin timezone
                'period'                         => $periodExpr,
                'store_id'                       => new Zend_Db_Expr('NULL'),
                'order_status'                   => 'o.status',
                'orders_count'                   => new Zend_Db_Expr('0'),
                'total_qty_ordered'              => new Zend_Db_Expr('0'),
                'total_qty_invoiced'             => new Zend_Db_Expr('0'),
                'total_income_amount'            => new Zend_Db_Expr(
                    sprintf('SUM((%s + %s - (%s + %s)) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_amount', 0),
                        $adapter->getIfNullSql('o.base_shipping_tax_amount', 0),
                        $adapter->getIfNullSql('o.base_shipping_canceled', 0),
                        $adapter->getCheckSql('o.base_shipping_canceled', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_revenue_amount'           => new Zend_Db_Expr('0'),
                'total_profit_amount'            => new Zend_Db_Expr('0'),
                'total_invoiced_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((%s + %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                        $adapter->getCheckSql('o.base_shipping_invoiced > 0', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_canceled_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((%s + %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_canceled', 0),
                        $adapter->getCheckSql('o.base_shipping_canceled > 0', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_paid_amount'              => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getCheckSql('o.base_total_paid > 0',
                            sprintf('%s + %s',
                                $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                                $adapter->getIfNullSql('o.base_shipping_tax_amount', 0)
                            ),
                            '0'
                        ),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_refunded_amount'          => new Zend_Db_Expr(
                    sprintf('SUM(%s * %s)',
                        $adapter->getIfNullSql('o.base_total_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount'               => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_tax_amount', 0),
                        $adapter->getCheckSql('o.base_shipping_canceled > 0', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_tax_amount_actual'        => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getCheckSql('o.base_shipping_invoiced > 0', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getCheckSql('o.base_shipping_refunded > 0', $adapter->getIfNullSql('o.base_shipping_tax_amount', 0), 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount'          => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_amount', 0),
                        $adapter->getIfNullSql('o.base_shipping_canceled', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_shipping_amount_actual'   => new Zend_Db_Expr(
                    sprintf('SUM((%s - %s) * %s)',
                        $adapter->getIfNullSql('o.base_shipping_invoiced', 0),
                        $adapter->getIfNullSql('o.base_shipping_refunded', 0),
                        $adapter->getIfNullSql('o.base_to_global_rate', 0)
                    )
                ),
                'total_discount_amount'          => new Zend_Db_Expr('0'),
                'total_discount_amount_actual'   => new Zend_Db_Expr('0')
            );

            $select          = $adapter->select();
            $selectOrderItem = $this->getTable('sales/order_item');

            $select->from(array('o' => $this->getTable('sales/order')), $columns)
                ->join(array('oi' => $selectOrderItem), 'oi.order_id = o.entity_id', array())
                ->where('oi.parent_item_id IS NULL')
                ->where('o.state NOT IN (?)', array(
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                    Mage_Sales_Model_Order::STATE_NEW
                ));

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                $periodExpr,
                //'o.store_id',
                'o.status',
            ));

            $adapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            // setup all columns to select SUM() except period, store_id and order_status
            foreach ($columns as $k => $v) {
                $columns[$k] = new Zend_Db_expr('SUM(' . $k . ')');
            }
            $columns['period']         = 'period';
            $columns['store_id']       = new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID);
            $columns['order_status']   = 'order_status';

            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where('store_id <> 0 OR store_id IS NULL');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'order_status'
            ));
            $adapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollBack();
            throw $e;
        }

        return $this;
    }
}
