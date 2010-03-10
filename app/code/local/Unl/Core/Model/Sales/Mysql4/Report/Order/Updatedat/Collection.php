<?php

class Unl_Core_Model_Sales_Mysql4_Report_Order_Updatedat_Collection extends Mage_Sales_Model_Mysql4_Report_Order_Updatedat_Collection
{
    protected $_selectedColumns = array(
        'total_qty_ordered'         => 'SUM(e.total_qty_ordered)',
        'base_profit_amount'        => 'SUM(IFNULL(e.base_subtotal_invoiced, 0) * e.base_to_global_rate) + SUM(IFNULL(e.base_discount_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_subtotal_refunded, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_discount_invoiced, 0) * e.base_to_global_rate) - SUM(IFNULL(e.base_total_invoiced_cost, 0) * e.base_to_global_rate)',
        'base_subtotal_amount'      => 'SUM(e.base_subtotal * e.base_to_global_rate)',
        'base_tax_amount'           => 'SUM(e.base_tax_amount * e.base_to_global_rate)',
        'base_shipping_amount'      => 'SUM(e.base_shipping_amount * e.base_to_global_rate)',
        'base_discount_amount'      => 'SUM(e.base_discount_amount * e.base_to_global_rate)',
        'base_grand_total_amount'   => 'SUM(e.base_grand_total * e.base_to_global_rate)',
        'base_invoiced_amount'      => 'SUM(e.base_total_paid * e.base_to_global_rate)',
        'base_refunded_amount'      => 'SUM(e.base_total_refunded * e.base_to_global_rate)',
        'base_canceled_amount'      => 'SUM(IFNULL(e.subtotal_canceled, 0) * e.base_to_global_rate)'
    );
    
    /**
     * Apply stores filter
     *
     * @return Mage_Sales_Model_Mysql4_Report_Order_Updatedat_Collection
     */
    protected function _applyStoresFilter()
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->getSelect()
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('oi.source_store_view IN(?) OR oi.source_store_view IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()
                ->join(array('oi' => $this->getTable('sales/order_item')), 'oi.order_id = e.entity_id AND oi.parent_item_id IS NULL', array())
                ->where('oi.source_store_view IN(?)', $storeIds);
        }

        return $this;
    }
    
    /**
     * Retrieve array of columns to select
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck || $storeIds[0] != '') {
            $this->_selectedColumns = array(
                'total_qty_ordered'         => 'SUM(oi.qty_ordered)',
                'base_profit_amount'        => 'SUM((oi.base_row_invoiced - oi.base_amount_refunded - (oi.base_cost * oi.qty_invoiced)) * e.base_to_global_rate)',
                'base_subtotal_amount'      => 'SUM(oi.base_row_total * e.base_to_global_rate)',
                'base_tax_amount'           => 'SUM(oi.base_tax_amount * e.base_to_global_rate)',
                'base_shipping_amount'      => new Zend_Db_Expr('0.0000'),
                'base_discount_amount'      => 'SUM(oi.base_discount_amount * e.base_to_global_rate)',
                'base_grand_total_amount'   => 'SUM((oi.base_row_total + oi.base_tax_amount - oi.base_discount_amount) * e.base_to_global_rate)',
                'base_invoiced_amount'      => 'SUM((oi.base_row_invoiced + oi.base_tax_invoiced - oi.base_discount_invoiced) * e.base_to_global_rate)',
                'base_refunded_amount'      => 'SUM(oi.base_amount_refunded * e.base_to_global_rate)',
                'base_canceled_amount'      => 'SUM(oi.base_price * oi.qty_canceled * e.base_to_global_rate)'
            );
        }
        
        if (!$this->isTotals()) {
            if ('month' == $this->_period) {
                $this->_periodFormat = 'DATE_FORMAT(e.updated_at, \'%Y-%m\')';
            } elseif ('year' == $this->_period) {
                $this->_periodFormat = 'EXTRACT(YEAR FROM e.updated_at)';
            } else {
                $this->_periodFormat = 'DATE(e.updated_at)';
            }
            $this->_selectedColumns += array(
                'period'       => $this->_periodFormat,
                'orders_count' => 'COUNT(DISTINCT(e.entity_id))'
            );
        }
        return $this->_selectedColumns;
    }
}