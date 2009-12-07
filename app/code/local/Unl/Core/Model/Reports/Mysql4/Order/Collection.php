<?php

class Unl_Core_Model_Reports_Mysql4_Order_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
    public function addOrdersCount($distinct=0)
    {
        $this->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        $what = "e.entity_id";
        if ($distinct) {
            $what = "DISTINCT(" . $what . ")";
        }
        $this->getSelect()
            ->from('', array("orders_count" => "COUNT({$what})"));

        return $this;
    }
    
    public function prepareSummary($range, $customStart, $customEnd, $isFilter=0, $websiteScope=1, $storeIds=array())
    {
        if ($websiteScope) {
            parent::prepareSummary($range, $customStart, $customEnd, $isFilter);
            if (!empty($storeIds)) {
                $this->addAttributeToFilter('store_id', array('in' => $storeIds));
            }
        } else {
            $this->filterSourceStore($storeIds);
            
            if ($isFilter==0) {
                $expr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))*{{base_to_global_rate}}";
                $attrs = array('base_to_global_rate');
                $this->addExpressionAttributeToSelect('revenue', "SUM({$expr})", $attrs);
            } else {
                $expr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))";
                $this->getSelect()
                    ->from("", array(
                        'revenue' => "SUM({$expr})"
                    ));
            }
            
            $this->addExpressionAttributeToSelect('quantity', 'COUNT(DISTINCT({{attribute}}))', 'entity_id')
                ->addExpressionAttributeToSelect('range', $this->_getRangeExpression($range), 'created_at')
                ->addAttributeToFilter('created_at', $this->getDateRange($range, $customStart, $customEnd))
                ->groupByAttribute('range')
                ->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                ->getSelect()->order('range', 'asc');
        }

        

        return $this;
    }
    
    public function filterSourceStore($storeIds)
    {
        $filter = $this->getConnection()->quoteInto(' AND order_item.source_store_view IN (?)', (array)$storeIds);
        $this->getSelect()
            ->joinInner(
                array('order_item' => $this->getTable('sales/order_item')), 
                "order_item.order_id = e.entity_id AND order_item.parent_item_id IS NULL" . $filter, 
                array());
        
        return $this;
    }
    
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to))
            ->addExpressionAttributeToSelect('orders', 'COUNT(DISTINCT({{entity_id}}))', array('entity_id'))
            ->getSelect()->group('("*")');
        
        return $this;
    }
    
    public function addQtyExpr()
    {
        $countSql = clone $this->getSelect();
        $countSql->reset();

        $countSql->from(array("order_items" => $this->getTable('sales/order_item')), array("sum(`order_items2`.`qty_ordered`)"))
            ->joinLeft(array("order_items2" => $this->getTable('sales/order_item')),
                "order_items2.item_id = `order_items`.item_id", array())
            ->where("`order_items`.`order_id` = `e`.`entity_id`")
            ->where("`order_items2`.`parent_item_id` is NULL");

        $this->getSelect()->from("", array("items" => "SUM((".$countSql."))"));
        
        return $this;
    }
    
    public function calculateSales($isFilter = 0, $websiteScope = 1, $storeIds = array())
    {
        if ($websiteScope) {
            parent::calculateSales($isFilter);
            if (!empty($storeIds)) {
                $this->addAttributeToFilter('store_id', array('in' => $storeIds));
            }
        } else {
            $this->filterSourceStore($storeIds);
                
            if ($isFilter) {
                $expr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))*{{base_to_global_rate}}";
                $attrs = array('base_to_global_rate');
                $this->addExpressionAttributeToSelect('lifetime', "SUM({$expr})", $attrs)
                    ->addExpressionAttributeToSelect('average', "SUM({$expr}) / COUNT(DISTINCT(e.entity_id))", $attrs);
            } else {
                $expr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))";
                $this->getSelect()
                    ->from("", array(
                        'lifetime' => "SUM({$expr})",
                        'average' => "SUM({$expr}) / COUNT(DISTINCT(e.entity_id))"
                    ));
            }
            
            $this->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                ->groupByAttribute('entity_type_id');
        }
        
        return $this;
    }
    
    public function calculateTotals($isFilter=0, $websiteScope=1, $storeIds=array())
    {
        if ($websiteScope) {
            parent::calculateTotals($isFilter);
            if (!empty($storeIds)) {
                $this->addAttributeToFilter('store_id', array('in' => $storeIds));
            }
        } else {
            $this->filterSourceStore($storeIds);
            
            if ($isFilter) {
                $revExpr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))*{{base_to_global_rate}}";
                $taxExpr = "(order_item.base_tax_amount)*{{base_to_global_rate}}";
                $attrs = array('base_to_global_rate');
                $this->addExpressionAttributeToSelect('revenue', "SUM({$revExpr})", $attrs)
                    ->addExpressionAttributeToSelect('tax', "SUM({$taxExpr})", $attrs);
            } else {
                $revExpr = "(order_item.base_row_total-IFNULL(order_item.base_amount_refunded,0)-IF(order_item.qty_canceled > 0, (order_item.base_row_total / order_item.qty_ordered * order_item.qty_canceled),0)-IFNULL(order_item.base_discount_amount,0))";
                $taxExpr = "(order_item.base_tax_amount)";
                $this->getSelect()
                    ->from("", array(
                        'revenue' => "SUM({$revExpr})",
                        'tax' => "SUM({$taxExpr})"
                    ));
            }
            
            $this->addExpressionAttributeToSelect('quantity', 'COUNT(DISTINCT({{entity_id}}))', array('entity_id'))
                ->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED))
                ->groupByAttribute('entity_type_id');
        }
        
        return $this;
    }
    
    public function setStoreIds($storeIds)
    {
        $vals = array_values($storeIds);
        if (count($storeIds) >= 1 && $vals[0] != '') {
            if (Mage::app()->getRequest()->getParam('website')) {
                $this->addAttributeToFilter('store_id', array('in' => (array)$storeIds))
                    ->addExpressionAttributeToSelect(
                        'subtotal',
                        'SUM({{base_subtotal}})',
                        array('base_subtotal'))
                    ->addExpressionAttributeToSelect(
                        'tax',
                        'SUM({{base_tax_amount}})',
                        array('base_tax_amount'))
                    ->addExpressionAttributeToSelect(
                        'shipping',
                        'SUM({{base_shipping_amount}})',
                        array('base_shipping_amount'))
                    ->addExpressionAttributeToSelect(
                        'discount',
                        'SUM({{base_discount_amount}})',
                        array('base_discount_amount'))
                    ->addExpressionAttributeToSelect(
                        'total',
                        'SUM({{base_grand_total}})',
                        array('base_grand_total'))
                    ->addExpressionAttributeToSelect(
                        'invoiced',
                        'SUM({{base_total_paid}})',
                        array('base_total_paid'))
                    ->addExpressionAttributeToSelect(
                        'refunded',
                        'SUM({{base_total_refunded}})',
                        array('base_total_refunded'));
            } else {
                $this->filterSourceStore($storeIds);
                                
                $this->getSelect()
                    ->from("", array("items" => "SUM(order_item.qty_ordered)"))
                    ->from("", array("subtotal" => "SUM(order_item.base_row_total)"))
                    ->from("", array("tax" => "SUM(order_item.base_tax_amount)"))
                    ->from("", array("shipping" => "0"))
                    ->from("", array("discount" => "SUM(order_item.base_discount_amount)"))
                    ->from("", array("total" => "SUM(order_item.base_row_total - order_item.base_discount_amount + order_item.base_tax_amount)"))
                    ->from("", array("invoiced" => "SUM(order_item.base_row_invoiced - order_item.base_discount_invoiced + order_item.base_tax_invoiced)"))
                    ->from("", array("refunded" => "SUM(order_item.base_amount_refunded)"));
                
                return $this;
            }
        } else {
            $this->addExpressionAttributeToSelect(
                    'subtotal',
                    'SUM({{base_subtotal}}/{{store_to_base_rate}})',
                    array('base_subtotal', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'tax',
                    'SUM({{base_tax_amount}}/{{store_to_base_rate}})',
                    array('base_tax_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'shipping',
                    'SUM({{base_shipping_amount}}/{{store_to_base_rate}})',
                    array('base_shipping_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'discount',
                    'SUM({{base_discount_amount}}/{{store_to_base_rate}})',
                    array('base_discount_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'total',
                    'SUM({{base_grand_total}}/{{store_to_base_rate}})',
                    array('base_grand_total', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'invoiced',
                    'SUM({{base_total_paid}}/{{store_to_base_rate}})',
                    array('base_total_paid', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'refunded',
                    'SUM({{base_total_refunded}}/{{store_to_base_rate}})',
                    array('base_total_refunded', 'store_to_base_rate'));
        }
        
        $this->addQtyExpr();

        return $this;
    }
}