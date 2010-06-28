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
        $qtyOrderedTableName = $this->getTable('sales/order_item');
        $qtyOrderedFieldName = 'qty_ordered';
        $productIdFieldName = 'product_id';

        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (e.type_id NOT IN (?))', $compositeTypeIds);

        if ($from != '' && $to != '') {
            $dateFilter = " AND `order`.created_at BETWEEN '{$from}' AND '{$to}'";
        } else {
            $dateFilter = "";
        }

        $this->getSelect()->reset()->from(
            array('order_items' => $qtyOrderedTableName),
            array('*', 'ordered_qty' => "order_items.{$qtyOrderedFieldName}")
        );

        $order = Mage::getResourceSingleton('sales/order');
        /* @var $order Mage_Sales_Model_Entity_Order */
        $stateAttr = $order->getAttribute('state');
        if ($stateAttr->getBackend()->isStatic()) {

            $_joinCondition = $this->getConnection()->quoteInto(
                'order.entity_id = order_items.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
            );
            $_joinCondition .= $dateFilter;

            $this->getSelect()->joinInner(
                array('order' => $this->getTable('sales/order')),
                $_joinCondition,
                array('ordernum' => 'increment_id')
            );
        } else {

            $_joinCondition = 'order.entity_id = order_state.entity_id';
            $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.attribute_id=? ', $stateAttr->getId());
            $_joinCondition .= $this->getConnection()->quoteInto(' AND order_state.value<>? ', Mage_Sales_Model_Order::STATE_CANCELED);

            $this->getSelect()
                ->joinInner(
                    array('order' => $this->getTable('sales/order')),
                    'order.entity_id = order_items.order_id' . $dateFilter,
                    array('ordernum' => 'increment_id'))
                ->joinInner(
                    array('order_state' => $stateAttr->getBackend()->getTable()),
                    $_joinCondition,
                    array());
        }
        
        $billingAddressAttr = $order->getAttribute('billing_address_id');
        
        $orderAddress = Mage::getResourceSingleton('sales/order_address');
        $firstNameAttr = $orderAddress->getAttribute('firstname');
        $lastNameAttr = $orderAddress->getAttribute('lastname');
        
        $this->getSelect()
            ->joinInner(array('_table_billing_address_id' => $billingAddressAttr->getBackendTable()), 'order.entity_id = _table_billing_address_id.entity_id AND _table_billing_address_id.entity_type_id = ' . $order->getEntityType()->getId() . ' AND _table_billing_address_id.attribute_id = ' . $billingAddressAttr->getId(), array())
            ->joinLeft(array('_table_customer_firstname' => $firstNameAttr->getBackendTable()), '_table_billing_address_id.value = _table_customer_firstname.entity_id AND _table_customer_firstname.entity_type_id = ' . $orderAddress->getEntityType()->getId() . ' AND _table_customer_firstname.attribute_id = ' . $firstNameAttr->getId(), array('customer_firstname' => 'value'))
            ->joinLeft(array('_table_customer_lastname' => $lastNameAttr->getBackendTable()), '_table_billing_address_id.value = _table_customer_lastname.entity_id AND _table_customer_lastname.entity_type_id = ' . $orderAddress->getEntityType()->getId() . ' AND _table_customer_lastname.attribute_id = ' . $lastNameAttr->getId(), array('customer_lastname' => 'value'))
            ->from('', array('customer' => new Zend_Db_Expr('CONCAT(_table_customer_firstname.value, " ", _table_customer_lastname.value)')));

        $sku = Mage::registry('filter_sku');
        if ($sku) {
            $this->getSelect()->where('order_items.sku = ?', $sku);
        }
        
        return $this;
    }
}