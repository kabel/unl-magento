<?php

class Unl_Core_Model_Mysql4_Picklist_Collection extends Mage_Reports_Model_Mysql4_Product_Collection
{
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*');
            
        $this->getSelect()->where("`order`.created_at BETWEEN '{$from}' AND '{$to}'");
        
        return $this;
    }
    
    public function setStoreIds($storeIds)
    {
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);
        
        $this->getSelect()
            ->joinInner(array('order_items' => $this->getTable('sales/order_item')), 
                "e.entity_id = order_items.product_id AND e.entity_type_id = {$this->getProductEntityTypeId()}{$productTypes}",
                array('qty_invoiced'));
        
        $vals = array_values($storeIds);
        if (count($storeIds) >= 1 && $vals[0] != '') {
            $this->getSelect()->where('order_items.source_store_view IN (?) OR order_items.source_store_view IS NULL', (array)$storeIds);
        }
        
        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $stateAttr = $order->getAttribute('state');
        if ($stateAttr->getBackend()->isStatic()) {

            $_joinCondition = $this->getConnection()->quoteInto(
                'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
            );

            $this->getSelect()->joinInner(
                array('order' => $this->getTable('sales/order')),
                $_joinCondition,
                array('order_num' => 'increment_id', 'order_id' => 'entity_id')
            );
        } else {

            $_joinCondition = 'order.entity_id = order_state.entity_id';
            $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.attribute_id=? ', $stateAttr->getId());
            $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.value<>? ', Mage_Sales_Model_Order::STATE_CANCELED);

            $this->getSelect()
                ->joinInner(
                    array('order' => $this->getTable('sales/order')),
                    'order.entity_id = order_items.order_id',
                    array('order_num' => 'increment_id'))
                ->joinInner(
                    array('order_state' => $stateAttr->getBackend()->getTable()),
                    $_joinCondition,
                    array());
        }
        
        $this->getSelect()
            ->joinLeft(
                array('store' => $this->getTable('core_store')),
                "order_items.source_store_view = store.store_id",
                array())
            ->joinLeft(
                array('stgroup' => $this->getTable('core_store_group')),
                "store.group_id = stgroup.group_id",
                array("merchant" => "stgroup.name"));
                
        return $this;
    }
}