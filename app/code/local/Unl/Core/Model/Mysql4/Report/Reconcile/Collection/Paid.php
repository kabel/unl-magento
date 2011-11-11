<?php

class Unl_Core_Model_Mysql4_Report_Reconcile_Collection_Paid extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Paid
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

        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns += array(
                'order_num' => 'o.increment_id'
            );
        }

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForProducts(true);
    }

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
            $this->getSelect()->where('oi.source_store_view IN(?) OR oi.source_store_view IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()->where('oi.source_store_view IN(?)', $storeIds);
        }

        return $this;
    }
}
