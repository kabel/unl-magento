<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Products_Refunded extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Refunded
{
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $aggregatedColumns = array(
            'items_count'     => 'COUNT(ci.entity_id)',
            'total_subtotal'  => 'SUM(ci.row_total * c.store_to_base_rate * c.base_to_global_rate)',
            'total_tax'       => 'SUM(IFNULL(ci.tax_amount, 0) * c.store_to_base_rate * c.base_to_global_rate)',
        	'total_discount'  => 'SUM(ABS(IFNULL(ci.discount_amount, 0)) * c.store_to_base_rate * c.base_to_global_rate)',
            'total_revenue'   => 'SUM((ci.row_total + IFNULL(ci.tax_amount, 0) - ABS(IFNULL(ci.discount_amount, 0))) * c.store_to_base_rate * c.base_to_global_rate)'
        );

        $this->_selectedColumns += $aggregatedColumns;

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForProducts();
    }
}