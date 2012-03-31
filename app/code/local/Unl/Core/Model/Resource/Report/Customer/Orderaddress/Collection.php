<?php

class Unl_Core_Model_Resource_Report_Customer_Orderaddress_Collection extends Mage_Sales_Model_Resource_Order_Address_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this
            ->addFilterToMap('order_date', 'order.created_at')
            ->addFilterToMap('ordernum', 'order.increment_id')
            ->addFilterToMap('order_state', 'order.state');

        $this->join(array('order' => 'sales/order'),
            "(main_table.parent_id = order.entity_id AND order.state != '" . Mage_Sales_Model_Order::STATE_CANCELED . "')",
            array('ordernum' => 'increment_id', 'order_date' => 'order.created_at')
        );

        $this->addFieldToFilter('address_type', 'billing');
        Mage::helper('unl_core')->addAdminScopeFilters($this, 'parent_id');

        return $this;
    }
}
