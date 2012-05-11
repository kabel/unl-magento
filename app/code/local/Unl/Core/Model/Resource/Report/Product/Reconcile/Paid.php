<?php

class Unl_Core_Model_Resource_Report_Product_Reconcile_Paid extends Mage_Sales_Model_Resource_Order_Invoice_Item_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $adapter = $this->getConnection();

        $this
            ->addFilterToMap('sku', 'main_table.sku')
            ->addFilterToMap('name', 'main_table.name')
            ->addFilterToMap('base_price', 'main_table.base_price')
            ->addFilterToMap('is_dummy', 'main_table.is_dummy')
            ->addFilterToMap('paid_date', 'i.paid_at')
            ->addFilterToMap('invoice_number', 'i.increment_id')
            ->addFilterToMap('invoice_state', 'i.state')
            ->addFilterToMap('unl_payment_account', 'oi.unl_payment_account')
            ->addFilterToMap('source_store_view', 'oi.source_store_view')
            ->addFilterToMap('payment_method', 'p.method');

        $this->getSelect()
            ->join(array('i' => $this->getTable('sales/invoice_grid')),
                '(main_table.parent_id = i.entity_id AND ' .
                    $adapter->prepareSqlCondition('i.state', array('nin' => array(
                        Mage_Sales_Model_Order_Invoice::STATE_OPEN,
                        Mage_Sales_Model_Order_Invoice::STATE_CANCELED
                    ))) . ')',
                array('parent_number' => 'increment_id', 'paid_date' => 'paid_at', 'parent_state' => 'state', 'order_number' => 'order_increment_id')
            )
            ->join(array('oi' => $this->getTable('sales/order_item')),
                '(main_table.order_item_id = oi.item_id)',
                array('unl_payment_account', 'source_store_view')
            )
            ->join(array('p' => $this->getTable('sales/order_payment')),
                'i.order_id = p.parent_id',
                array('payment_method' => 'method')
            );

        $grossExpr = '(main_table.base_row_total - IFNULL(main_table.base_discount_amount, 0))';
        $this->addFilterToMap('base_row_gross', $grossExpr);
        $this->addExpressionFieldToSelect('base_row_gross', $grossExpr, array());

        $discountExpr = 'IFNULL(main_table.base_discount_amount, 0)';
        $this->addFilterToMap('base_discount_amount', $discountExpr);
        $this->addExpressionFieldToSelect('base_discount_amount', $discountExpr, array());

        $this->addFieldToFilter('is_dummy', false);

        if ($scope = Mage::helper('unl_core')->getScopeFilteredStores()) {
            $this->addFieldToFilter('source_store_view', array('in' => $scope));
        }

        return $this;
    }
}
