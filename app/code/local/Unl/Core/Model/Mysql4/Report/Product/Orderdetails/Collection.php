<?php

class Unl_Core_Model_Mysql4_Report_Product_Orderdetails_Collection extends Mage_Sales_Model_Mysql4_Order_Item_Collection
{

    /**
     * Set Date range to collection
     *
     * @param int $from
     * @param int $to
     * @return Unl_Core_Model_Mysql4_Report_Product_Orderdetails_Collection
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()
            ->addOrderDetails($from, $to)
            ->setOrder('ordered_qty', 'desc');

        return $this;
    }

    /**
     * Set Store filter to collection
     *
     * @param array $storeIds
     * @return Unl_Core_Model_Mysql4_Report_Product_Orderdetails_Collection
     */
    public function setStoreIds($storeIds)
    {
        $storeId = array_pop($storeIds);
        if ($storeId) {
            $this->getSelect()->where('order_items.source_store_view = ?', $storeId);
        }
        return $this;
    }

    /**
     *
     * @param int $from
     * @param int $to
     * @return Unl_Core_Model_Mysql4_Report_Product_Orderdetails_Collection
     */
    public function addOrderDetails($from, $to)
    {
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (order_items.product_type NOT IN (?))', $compositeTypeIds);

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $this->getSelect()->reset()->from(
            array('order_items' => $this->getTable('sales/order_item')),
            array('*', 'ordered_qty' => 'order_items.qty_ordered'));

        $_joinCondition = $this->getConnection()->quoteInto(
            'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
        );
        $_joinCondition .= $dateFilter . $productTypes;

        $this->getSelect()->joinInner(
            array('order' => $this->getTable('sales/order')),
            $_joinCondition,
            array('ordernum' => 'increment_id')
        );

        $this->getSelect()
            ->joinInner(array('_table_billing_address' => $this->getTable('sales/order_address')), "order.entity_id = _table_billing_address.parent_id AND _table_billing_address.address_type = 'billing'", array('customer_firstname' => new Zend_Db_Expr('CASE WHEN order.customer_id IS NULL THEN _table_billing_address.firstname ELSE order.customer_firstname END'), 'customer_lastname' => new Zend_Db_Expr('CASE WHEN order.customer_id IS NULL THEN _table_billing_address.lastname ELSE order.customer_lastname END')));

        $sku = Mage::registry('filter_sku');
        if ($sku) {
            $this->getSelect()->where('order_items.sku LIKE ?', $sku . '%');
        }

        return $this;
    }
}
