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

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForProducts();
    }

    protected function _initSelectForProducts($groupOrder = true)
    {
        $this->_initSelectForShipping($groupOrder);

        $this->getSelect()
            ->join(array('ii' => $this->getTable('sales/invoice_item')), 'i.entity_id = ii.parent_id', array())
            ->join(array('oi' => $this->getTable('sales/order_item')), 'ii.order_item_id = oi.item_id AND oi.parent_item_id IS NULL', array());

        return $this;
    }

    protected function _initSelectForShipping($groupOrder = true)
    {
        $this->getSelect()
            ->from(array('i' => $this->getResource()->getMainTable()) , $this->_getSelectedColumns())
            ->join(array('p' => $this->getTable('sales/order_payment')), 'i.order_id = p.parent_id', array())
            ->where('p.method IN (?)', $this->_paymentMethodCodes);

        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);

            if (!$this->isSubTotals() && $groupOrder) {
                $this->getSelect()
                    ->join(array('o' => $this->getTable('sales/order')), 'i.order_id = o.entity_id', array('order_num' => 'increment_id'))
                    ->group('o.entity_id');
            }
        } else {
            $this->getSelect()->having('COUNT(*) > 0');
        }

        return $this;
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
