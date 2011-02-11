<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Shipping_Paid extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Paid
{
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $aggregatedColumns = array(
            'orders_count'    => 'COUNT(DISTINCT(i.order_id))',
            'total_tax'       => 'SUM(i.shipping_tax_amount * i.store_to_base_rate * i.base_to_global_rate)',
            'total_revenue'   => 'SUM((i.shipping_amount + i.shipping_tax_amount) * i.store_to_base_rate * i.base_to_global_rate)'
        );

        $this->_selectedColumns += $aggregatedColumns;

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForShipping();
    }
}