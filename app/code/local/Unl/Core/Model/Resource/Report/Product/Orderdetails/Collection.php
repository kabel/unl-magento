<?php

class Unl_Core_Model_Resource_Report_Product_Orderdetails_Collection extends Mage_Sales_Model_Resource_Order_Item_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $adapter = $this->getConnection();

        $this
            ->addFilterToMap('order_date', 'order.created_at')
            ->addFilterToMap('ordernum', 'order.increment_id')
            ->addFilterToMap('order_state', 'order.state')
            ->addFilterToMap('order_status', 'order.status')
            ->addFilterToMap('customer_email', 'order.customer_email');

        $this
            ->addFilterToMap('billing_firstname', 'oa.firstname')
            ->addFilterToMap('billing_lastname', 'oa.lastname');

        $this->getSelect()
            ->join(array('order' => $this->getTable('sales/order')),
                "(main_table.order_id = order.entity_id AND order.state != '" . Mage_Sales_Model_Order::STATE_CANCELED ."')",
                array('ordernum' => 'increment_id', 'order_date' => 'created_at', 'order_status' => 'status', 'customer_email')
            )
            ->joinLeft(array('oa' => $this->getTable('sales/order_address')),
                "(main_table.order_id = oa.parent_id AND oa.address_type = 'billing')",
                array()
            );

        $expressions = array(
            'customer_firstname' => array(
                'order.customer_id',
                'oa.firstname',
                'order.customer_firstname'
            ),
            'customer_lastname' => array(
                'order.customer_id',
                'oa.lastname',
                'order.customer_lastname'
            )
        );
        foreach ($expressions as $field => $fields) {
            //$this->addFilterToMap($field, $adapter->getCheckSql($fields[0] . 'IS NULL', $fields[1], $fields[2]));
            $this->addExpressionFieldToSelect($field,
                $adapter->getCheckSql('{{cid}} IS NULL', '{{b}}', '{{c}}'),
                array_combine(array('cid', 'b', 'c'), $fields)
            );
        }

        $this->addFilterToMap('qty_adjusted', '(qty_ordered - qty_canceled)');
        $this->addExpressionFieldToSelect('qty_adjusted', '({{ordered}} - {{canceled}})', array(
            'ordered'  => 'qty_ordered',
            'canceled' => 'qty_canceled'
        ));

        $this->addFilterToMap('base_total', '(main_table.base_row_total - main_table.base_discount_amount + main_table.base_tax_amount)');
        $this->addExpressionFieldToSelect('base_total', '({{row}} - {{discount}} + {{tax}})', array(
            'row'  => 'main_table.base_row_total',
            'discount' => 'main_table.base_discount_amount',
            'tax' => 'main_table.base_tax_amount',
        ));

        $this->addFieldToFilter('qty_adjusted', array('gt' => 0));

        $this->addFieldToFilter('is_dummy', false);

        Mage::helper('unl_core')->addProductAdminScopeFilters($this);

        return $this;
    }
}
