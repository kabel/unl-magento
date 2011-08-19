<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Shipping_Refunded extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Refunded
{
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $aggregatedColumns = array(
            'orders_count'       => 'COUNT(DISTINCT(c.order_id))',
            'total_tax'          => 'SUM(c.shipping_tax_amount * c.store_to_base_rate * c.base_to_global_rate)',
            'total_shipping'     => 'SUM(c.shipping_amount * c.store_to_base_rate * c.base_to_global_rate)',
            'total_revenue'      => 'SUM((c.shipping_amount + c.shipping_tax_amount) * c.store_to_base_rate * c.base_to_global_rate)',
            'total_adjustments'  => 'SUM(c.adjustment * c.store_to_base_rate * c.base_to_global_rate)'
        );

        $this->_selectedColumns += $aggregatedColumns;

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForShipping();
    }
}
