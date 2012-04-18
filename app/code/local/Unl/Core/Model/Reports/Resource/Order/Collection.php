<?php

class Unl_Core_Model_Reports_Resource_Order_Collection extends Mage_Reports_Model_Resource_Order_Collection
{
    /* Overrides
     * @see Mage_Reports_Model_Resource_Order_Collection::_prepareSummaryLive()
     * by using order item totals on filter
     */
    protected function _prepareSummaryLive($range, $customStart, $customEnd, $isFilter = 0)
    {
        $this->setMainTable('sales/order');
        $adapter = $this->getConnection();

        /**
         * Reset all columns, because result will group only by 'created_at' field
         */
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS);

        $expression = sprintf('%s - %s - %s - (%s - %s - %s)',
            $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
            $adapter->getIfNullSql('main_table.base_total_refunded', 0),
            $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
            $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
        );
        if ($isFilter == 0) {
            $this->getSelect()->columns(array(
                'revenue' => new Zend_Db_Expr(
                    sprintf('SUM((%s) * %s)', $expression,
                        $adapter->getIfNullSql('main_table.base_to_global_rate', 0)
                    )
                 )
            ));
        } else {
            $expression = sprintf('%s - ABS(%s) - %s',
                $adapter->getIfNullSql('oi.base_row_invoiced', 0),
				$adapter->getIfNullSql('oi.base_discount_invoiced', 0),
				$adapter->getIfNullSql('oi.base_amount_refunded', 0)
			);
            $this->addFilterToMap('created_at', 'main_table.created_at')
                ->addFilterToMap('store_id', 'oi.source_store_view');
			$this->getSelect()
			    ->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array())
			    ->where('oi.parent_item_id IS NULL');

			$this->getSelect()->columns(array(
				'revenue' => new Zend_Db_Expr(sprintf('SUM(%s)', $expression))
			));
        }

        $dateRange = $this->getDateRange($range, $customStart, $customEnd);

        $tzRangeOffsetExpression = $this->_getTZRangeOffsetExpression(
            $range, 'created_at', $dateRange['from'], $dateRange['to']
        );

        $this->getSelect()
            ->columns(array(
                'quantity' => 'COUNT(' . ($isFilter ? 'DISTINCT ' : '') . 'main_table.entity_id)',
                'range' => $tzRangeOffsetExpression,
            ))
            ->where('main_table.state NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_NEW)
            )
            ->order('range', Zend_Db_Select::SQL_ASC)
            ->group($tzRangeOffsetExpression);

        $this->addFieldToFilter('main_table.created_at', $dateRange);

        return $this;
    }

    /* Overrides
     * @see Mage_Reports_Model_Resource_Order_Collection::calculateSales()
     * by using order items during live filter
     */
    public function calculateSales($isFilter = 0)
    {
        $statuses = Mage::getSingleton('sales/config')
            ->getOrderStatusesForState(Mage_Sales_Model_Order::STATE_CANCELED);

        if (empty($statuses)) {
            $statuses = array(0);
        }
        $adapter = $this->getConnection();

        if (Mage::getStoreConfig('sales/dashboard/use_aggregated_data')) {
            $this->setMainTable('sales/order_aggregated_created');
            $this->removeAllFieldsFromSelect();
            $averageExpr = $adapter->getCheckSql(
                'SUM(main_table.orders_count) > 0',
                'SUM(main_table.total_revenue_amount)/SUM(main_table.orders_count)',
                0);
            $this->getSelect()->columns(array(
                'lifetime' => 'SUM(main_table.total_revenue_amount)',
                'average'  => $averageExpr
            ));

            if (!$isFilter) {
                $this->addFieldToFilter('store_id',
                    array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
                );
            }
            $this->getSelect()->where('main_table.order_status NOT IN(?)', $statuses);
        } else {
            $this->setMainTable('sales/order');
            $this->removeAllFieldsFromSelect();

            $expr = sprintf('%s - %s - %s - (%s - %s - %s)',
                $adapter->getIfNullSql('main_table.base_total_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_tax_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0),
                $adapter->getIfNullSql('main_table.base_total_refunded', 0),
                $adapter->getIfNullSql('main_table.base_tax_refunded', 0),
                $adapter->getIfNullSql('main_table.base_shipping_refunded', 0)
            );

            if ($isFilter == 0) {
                $expr = '(' . $expr . ') * main_table.base_to_global_rate';
                $avgExpr = "AVG({$expr})";
            } else {
                $this->addFilterToMap('created_at', 'main_table.created_at')
                    ->addFilterToMap('store_id', 'oi.source_store_view');
                $this->getSelect()
                    ->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array())
                    ->where('oi.parent_item_id IS NULL');

                $expr = sprintf('%s - ABS(%s) - %s',
                    $adapter->getIfNullSql('oi.base_row_invoiced', 0),
                    $adapter->getIfNullSql('oi.base_discount_invoiced', 0),
                    $adapter->getIfNullSql('oi.base_amount_refunded', 0)
                );
                $avgExpr = "SUM({$expr}) / COUNT(DISTINCT main_table.entity_id)";
            }

            $this->getSelect()
                ->columns(array(
                    'lifetime' => "SUM({$expr})",
                    'average'  => $avgExpr
                ))
                ->where('main_table.status NOT IN(?)', $statuses)
                ->where('main_table.state NOT IN(?)', array(
                    Mage_Sales_Model_Order::STATE_NEW,
                    Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                );
        }
        return $this;
    }

    /* Overrides
     * @see Mage_Reports_Model_Resource_Order_Collection::_calculateTotalsLive()
     * by using order item totals for filter
     */
    protected function _calculateTotalsLive($isFilter = 0)
    {
        $this->setMainTable('sales/order');
        $this->removeAllFieldsFromSelect();

        $adapter = $this->getConnection();

        $baseTotalInvoiced    = $adapter->getIfNullSql('main_table.base_total_invoiced', 0);
        $baseTotalRefunded    = $adapter->getIfNullSql('main_table.base_total_refunded', 0);
        $baseTaxInvoiced      = $adapter->getIfNullSql('main_table.base_tax_invoiced', 0);
        $baseTaxRefunded      = $adapter->getIfNullSql('main_table.base_tax_refunded', 0);
        $baseShippingInvoiced = $adapter->getIfNullSql('main_table.base_shipping_invoiced', 0);
        $baseShippingRefunded = $adapter->getIfNullSql('main_table.base_shipping_refunded', 0);

        $revenueExp = sprintf('%s - %s - %s - (%s - %s - %s)',
            $baseTotalInvoiced,
            $baseTaxInvoiced,
            $baseShippingInvoiced,
            $baseTotalRefunded,
            $baseTaxRefunded,
            $baseShippingRefunded
        );
        $taxExp = sprintf('%s - %s', $baseTaxInvoiced, $baseTaxRefunded);
        $shippingExp = sprintf('%s - %s', $baseShippingInvoiced, $baseShippingRefunded);

        if ($isFilter == 0) {
            $rateExp = $adapter->getIfNullSql('main_table.base_to_global_rate', 0);
            $this->getSelect()->columns(
                array(
                    'revenue'  => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $revenueExp, $rateExp)),
                    'tax'      => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $taxExp, $rateExp)),
                    'shipping' => new Zend_Db_Expr(sprintf('SUM((%s) * %s)', $shippingExp, $rateExp))
                )
            );
        } else {
            $revenueExp = sprintf('%s - ABS(%s) - %s',
                $adapter->getIfNullSql('oi.base_row_invoiced', 0),
				$adapter->getIfNullSql('oi.base_discount_invoiced', 0),
				$adapter->getIfNullSql('oi.base_amount_refunded', 0)
			);
            $taxExp = sprintf('%s - (%s / %s)',
				$adapter->getIfNullSql('oi.base_tax_invoiced', 0),
				$adapter->getIfNullSql('oi.tax_refunded', 0),
				$adapter->getIfNullSql('main_table.base_to_order_rate', 1)
			);
			$shippingExp = '0';
            $this->addFilterToMap('created_at', 'main_table.created_at')
                ->addFilterToMap('store_id', 'oi.source_store_view');
            $this->getSelect()
                ->joinInner(array('oi' => $this->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array())
                ->where('oi.parent_item_id IS NULL');

            $this->getSelect()->columns(
                array(
                    'revenue'  => new Zend_Db_Expr(sprintf('SUM(%s)', $revenueExp)),
                    'tax'      => new Zend_Db_Expr(sprintf('SUM(%s)', $taxExp)),
                    'shipping' => new Zend_Db_Expr(sprintf('SUM(%s)', $shippingExp))
                )
            );
        }

        $this->getSelect()->columns(array(
            'quantity' => 'COUNT(' . ($isFilter ? 'DISTINCT ' : '') . 'main_table.entity_id)'
        ))
        ->where('main_table.state NOT IN (?)', array(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_NEW)
         );

        return $this;
    }
}
