<?php

class Unl_Core_Model_Mysql4_Picklist_Collection extends Mage_Sales_Model_Mysql4_Order_Invoice_Item_Collection
{
    public function setDateRange($from, $to)
    {
        $this->_reset();
        $this->getSelect()
            ->joinInner(array('invoice' => $this->getTable('sales/invoice')),
                'main_table.parent_id = `invoice`.entity_id',
                array())
            ->where("invoice.created_at BETWEEN '{$from}' AND '{$to}'");

        return $this;
    }

    public function setStoreIds($storeIds)
    {
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (`order_items`.product_type NOT IN (?))', $compositeTypeIds);

        $this->getSelect()
            ->joinInner(array('order_items' => $this->getTable('sales/order_item')),
                "main_table.order_item_id = `order_items`.item_id{$productTypes}",
                array('order_id'))
            ->joinInner(array('o' => $this->getTable('sales/order')),
                'order_items.order_id = o.entity_id',
                array('order_num' => 'increment_id'));

        $vals = array_values($storeIds);
        if (count($storeIds) >= 1 && $vals[0] != '') {
            $this->getSelect()->where('order_items.source_store_view IN (?) OR order_items.source_store_view IS NULL', (array)$storeIds);
        }

        $this->getSelect()
            ->joinLeft(
                array('store' => $this->getTable('core/store')),
                "order_items.source_store_view = store.store_id",
                array())
            ->joinLeft(
                array('stgroup' => $this->getTable('core/store_group')),
                "store.group_id = stgroup.group_id",
                array("merchant" => "stgroup.name"));

        return $this;
    }
}
