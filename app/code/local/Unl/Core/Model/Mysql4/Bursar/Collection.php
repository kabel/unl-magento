<?php

class Unl_Core_Model_Mysql4_Bursar_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to))
            ->addExpressionAttributeToSelect('orders', 'COUNT(DISTINCT({{entity_id}}))', array('entity_id'));
        
        return $this;
    }
    
    /*public function addQtyExpr()
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
    }*/
    
    public function setStoreIds($storeIds)
    {
        $vals = array_values($storeIds);
        
        $this->getSelect()
            ->from("", array("subtotal" => "SUM(order_item.base_row_total)"))
            ->from("", array("tax" => "SUM(order_item.base_tax_amount)"))
            ->from("", array("shipping" => "0"))
            ->from("", array("total" => "SUM(order_item.base_row_total - order_item.base_discount_amount + order_item.base_tax_amount)"))
            ->from("", array("invoiced" => "SUM(order_item.base_row_invoiced - order_item.base_discount_invoiced + order_item.base_tax_invoiced)"))
            ->from("", array("refunded" => "SUM(order_item.base_amount_refunded)"))
            ->joinInner(
                array('order_item' => $this->getTable('sales/order_item')), 
                "order_item.order_id = e.entity_id AND order_item.parent_item_id IS NULL", 
                array('entity_id' => 'order_item.item_id'))
            ->joinLeft(
                array('store' => $this->getTable('core_store')),
                "order_item.source_store_view = store.store_id",
                array())
            ->joinLeft(
                array('stgroup' => $this->getTable('core_store_group')),
                "store.group_id = stgroup.group_id",
                array("merchant" => "stgroup.name"))
            ->group("stgroup.group_id");
            
        $this->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        //$temp = $this->getSelect()->__toString();
                
        return $this;
    }
}