<?php

class Unl_Core_Model_Tax_Mysql4_Tax extends Mage_Tax_Model_Mysql4_Tax
{
/**
     * Aggregate Tax data
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Tax_Model_Mysql4_Tax
     */
    public function aggregate($from = null, $to = null)
    {
        try {
            if (!is_null($from)) {
                $from = $this->formatDate($from);
            }
            if (!is_null($to)) {
                $from = $this->formatDate($to);
            }
            $tableName = $this->getTable('tax/tax_order_aggregated_created');
            $writeAdapter = $this->_getWriteAdapter();

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
                'period'                => 'DATE(created_at)',
                'store_id'              => 'store_id',
                'code'                  => 'tax.code',
                'order_status'          => 'e.status',
                'percent'               => 'tax.percent',
                'orders_count'          => 'COUNT(DISTINCT(e.entity_id))',
                'tax_base_amount_sum'   => 'SUM(tax.base_real_amount * e.base_to_global_rate)',
                'base_sales_amount_sum' => 'SUM(tax.base_sale_amount * e.base_to_global_rate)'
            );

            $select = $writeAdapter->select()
                ->from(array('e' => $this->getTable('sales/order')), $columns)
                ->joinInner(array('tax' => $this->getTable('sales/order_tax')), 'e.entity_id = tax.order_id', array());

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(e.created_at) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(new Zend_Db_Expr('1,2,3'));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

            $select = $writeAdapter->select();

            $columns = array(
                'period'                => 'period',
                'store_id'              => new Zend_Db_Expr('0'),
                'code'                  => 'code',
                'order_status'          => 'order_status',
                'percent'               => 'percent',
                'orders_count'          => 'SUM(orders_count)',
                'tax_base_amount_sum'   => 'SUM(tax_base_amount_sum)',
                'base_sales_amount_sum' => 'SUM(base_sales_amount_sum)'
            );

            $select
                ->from($tableName, $columns)
                ->where("store_id <> 0");

                if (!is_null($from) || !is_null($to)) {
                    $select->where("DATE(period) IN(?)", new Zend_Db_Expr($subQuery));
                }

                $select->group(array(
                    'period',
                    'code',
                    'order_status'
                ));

            $writeAdapter->query("
                INSERT INTO `{$tableName}` (" . implode(',', array_keys($columns)) . ") {$select}
            ");

            $reportsFlagModel = Mage::getModel('reports/flag');
            $reportsFlagModel->setReportFlagCode(Mage_Reports_Model_Flag::REPORT_TAX_FLAG_CODE);
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