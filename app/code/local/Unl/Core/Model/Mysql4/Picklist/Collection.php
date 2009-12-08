<?php

class Unl_Core_Model_Mysql4_Picklist_Collection extends Mage_Sales_Model_Mysql4_Order_Invoice_Item_Collection
{
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addAttributeToSelect('*');

        $invoice = Mage::getResourceSingleton('sales/order_invoice');
        $this->getSelect()
            ->joinInner(array('invoice' => $invoice->getEntityTable()),
                "`e`.parent_id = `invoice`.entity_id AND `invoice`.entity_type_id = {$invoice->getTypeId()}",
                array())
            ->where("`invoice`.created_at BETWEEN '{$from}' AND '{$to}'");
        
        return $this;
    }
    
    public function setStoreIds($storeIds)
    {
        $orderItemIdAttr = $this->getAttribute('order_item_id');
        $orderItemIdCol = '`e`.order_item_id';
        if (!$orderItemIdAttr->getBackend()->isStatic()) {
            $orderItemIdCol = 'order_item_id.value';
            $_joinCondition = 'e.entity_id = order_item_id.entity_id';
            $_joinCondition .= $this->getConnection()->quoteInto(' AND order_item_id.attribute_id=? ', $orderItemIdAttr->getId());

            $this->getSelect()
                ->joinInner(
                    array('order_item_id' => $orderItemIdAttr->getBackend()->getTable()),
                    $_joinCondition,
                    array());
        }
        
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (`order_items`.product_type NOT IN (?))', $compositeTypeIds);
        
        $this->getSelect()
            ->joinInner(array('order_items' => $this->getTable('sales/order_item')), 
                "{$orderItemIdCol} = `order_items`.item_id{$productTypes}",
                array());
        
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
                    array('order_num' => 'increment_id', 'order_id' => 'entity_id'))
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