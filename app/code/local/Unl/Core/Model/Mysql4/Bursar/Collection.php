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
        $product = Mage::getResourceSingleton('catalog/product');
        
        $this->getSelect()
            ->from("", array("tax" => "SUM(order_item.base_tax_amount)"))
            ->from("", array("shipping" => "0"))
            ->from("", array("total" => "SUM(order_item.base_row_total - order_item.base_discount_amount + order_item.base_tax_amount)"))
            ->from("", array("invoiced" => "SUM(order_item.base_row_invoiced - order_item.base_discount_invoiced + order_item.base_tax_invoiced)"))
            ->from("", array("refunded" => "SUM(order_item.base_amount_refunded)"))
            ->joinInner(
                array('order_item' => $this->getTable('sales/order_item')), 
                "order_item.order_id = e.entity_id AND order_item.parent_item_id IS NULL", 
                array('entity_id' => 'order_item.item_id'))
            ->joinInner(
                array('product_int' => $this->getTable('catalog_product_entity_int')),
                "order_item.product_id = product_int.entity_id AND product_int.entity_type_id = {$product->getTypeId()}",
                array())
            ->joinInner(
                array('eav' => $this->getTable('eav_attribute')),
                "eav.attribute_id = product_int.attribute_id AND eav.attribute_code = 'source_store_view'",
                array())
            ->joinInner(
                array('store' => $this->getTable('core_store')),
                "product_int.value = store.store_id",
                array())
            ->joinInner(
                array('stgroup' => $this->getTable('core_store_group')),
                "store.group_id = stgroup.group_id",
                array("merchant" => "stgroup.name"))
            ->group("stgroup.group_id");
            
        $this->addAttributeToFilter('state', array('neq' => Mage_Sales_Model_Order::STATE_CANCELED));
        //$temp = $this->getSelect()->__toString();
                
        return $this;
    }
}