<?php

class Unl_Core_Model_Resource_Report_Product_Reconcile_Refunded extends Mage_Sales_Model_Resource_Order_Creditmemo_Item_Collection
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
            ->addFilterToMap('paid_date', 'c.refunded_at')
            ->addFilterToMap('parent_number', 'c.increment_id')
            ->addFilterToMap('parent_state', 'c.state')
            ->addFilterToMap('unl_payment_account', 'oi.unl_payment_account')
            ->addFilterToMap('source_store_view', 'oi.source_store_view');

        $this->getSelect()
            ->join(array('c' => $this->getTable('sales/creditmemo')),
                '(main_table.parent_id = c.entity_id)',
                array('parent_number' => 'increment_id', 'paid_date' => 'refunded_at', 'parent_state' => 'state')
            )
            ->join(array('oi' => $this->getTable('sales/order_item')),
                '(main_table.order_item_id = oi.item_id)',
                array('unl_payment_account', 'source_store_view')
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
