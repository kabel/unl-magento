<?php

class Unl_Core_Model_Reports_Mysql4_Order_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
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
                $product = Mage::getResourceSingleton('catalog/product');
                
                $this->getSelect()
                    ->from("", array("items" => "SUM(order_item.qty_ordered)"))
                    ->from("", array("subtotal" => "SUM(order_item.base_row_total)"))
                    ->from("", array("tax" => "SUM(order_item.base_tax_amount)"))
                    ->from("", array("shipping" => "0"))
                    ->from("", array("discount" => "SUM(order_item.base_discount_amount)"))
                    ->from("", array("total" => "SUM(order_item.base_row_total - order_item.base_discount_amount + order_item.base_tax_amount)"))
                    ->from("", array("invoiced" => "SUM(order_item.base_row_invoiced)"))
                    ->from("", array("refunded" => "SUM(order_item.base_amount_refunded)"))
                    ->joinInner(
                        array('order_item' => $this->getTable('sales/order_item')), 
                        "order_item.order_id = e.entity_id AND order_item.parent_item_id IS NULL", 
                        array())
                    ->joinInner(
                        array('product_int' => $this->getTable('catalog_product_entity_int')),
                        "order_item.product_id = product_int.entity_id AND product_int.entity_type_id = {$product->getTypeId()}",
                        array())
                    ->joinInner(
                        array('eav' => $this->getTable('eav_attribute')),
                        "eav.attribute_id = product_int.attribute_id AND eav.attribute_code = 'source_store_view'",
                        array())
                    ->where("product_int.value IN (?)", (array)$storeIds);
                    
                //$temp = $this->getSelect()->__toString();
                
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