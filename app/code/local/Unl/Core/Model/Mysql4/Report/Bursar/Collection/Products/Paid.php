<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Products_Paid extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Paid
{
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $aggregatedColumns = array(
            'items_count'     => 'COUNT(ii.entity_id)',
            'total_subtotal'  => 'SUM(ii.row_total * i.store_to_base_rate * i.base_to_global_rate)',
            'total_tax'       => 'SUM(IFNULL(ii.tax_amount, 0) * i.store_to_base_rate * i.base_to_global_rate)',
            'total_discount'  => 'SUM(ABS(IFNULL(ii.discount_amount, 0)) * i.store_to_base_rate * i.base_to_global_rate)',
            'total_revenue'   => 'SUM((ii.row_total + IFNULL(ii.tax_amount, 0) - ABS(IFNULL(ii.discount_amount, 0))) * i.store_to_base_rate * i.base_to_global_rate)'
        );

        $this->_selectedColumns += $aggregatedColumns;

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForProducts();
    }
}
