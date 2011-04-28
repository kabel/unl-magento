<?php

class Unl_Core_Model_Tax_Mysql4_Report_Tax extends Mage_Tax_Model_Mysql4_Report_Tax
{
    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Report_Tax::aggregate()
     * to also aggregate the sales_amount
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();

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
                'period'                => "DATE(CONVERT_TZ(e.created_at, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))",
                'store_id'              => 'e.store_id',
                'code'                  => 'tax.code',
                'order_status'          => 'e.status',
                'percent'               => 'tax.percent',
                'orders_count'          => 'COUNT(DISTINCT(e.entity_id))',
                'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)',
                // additional column
                'base_sales_amount_sum' => 'SUM(tax.base_sale_amount * e.base_to_global_rate)'
            );

            $select = $writeAdapter->select();
            $select->from(array('tax' => $this->getTable('tax/sales_order_tax')), $columns)
                ->joinInner(array('e' => $this->getTable('sales/order')), 'e.entity_id = tax.order_id', array())
                ->useStraightJoin();

            $select->where('e.state NOT IN (?)', array(
                Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
                Mage_Sales_Model_Order::STATE_NEW
            ));

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'e.created_at'));
            }

            $select->group(array('period', 'store_id', 'code', 'tax.percent', 'order_status'));

            $writeAdapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            $select->reset();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new Zend_Db_Expr('0'),
                'code'                  => 'code',
                'order_status'          => 'order_status',
                'percent'               => 'percent',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)',
                // additional column
                'base_sales_amount_sum' => 'SUM(base_sales_amount_sum)'
            );

            $select
                ->from($this->getMainTable(), $columns)
                ->where('store_id <> 0');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array('period', 'code', 'percent', 'order_status'));

            $writeAdapter->query($select->insertFromSelect($this->getMainTable(), array_keys($columns)));

            $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_TAX_FLAG_CODE);
        } catch (Exception $e) {
            $writeAdapter->rollBack();
            throw $e;
        }

        $writeAdapter->commit();
        return $this;
    }
}