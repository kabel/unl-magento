<?php

class Unl_Core_Helper_Report_Bursar extends Mage_Core_Helper_Abstract
{
    protected $_paymentTypeMap = array(
        'cc'    => array('paypal_direct', 'paypal_express', 'paypaluk_direct', 'paypaluk_express', 'verisign'),
        'co'    => array('purchaseorder'),
        'nocap' => array('checkmo', 'cash', 'invoicelater'),
    );

    public function getShippingAggregateColumns()
    {
        return array(
            'orders_count'    => 'COUNT(DISTINCT e.order_id)',
            'total_tax'       => 'SUM(IFNULL(e.shipping_tax_amount, 0) * e.store_to_base_rate * e.base_to_global_rate)',
            'total_shipping'  => 'SUM(e.shipping_amount * e.store_to_base_rate * e.base_to_global_rate)',
            'total_revenue'   => 'SUM((e.shipping_amount + IFNULL(e.shipping_tax_amount, 0)) * e.store_to_base_rate * e.base_to_global_rate)'
        );
    }

    public function getItemAggregateColumns()
    {
        return array(
            'items_count'     => 'COUNT(ei.entity_id)',
            'total_subtotal'  => 'SUM(ei.row_total * e.store_to_base_rate * e.base_to_global_rate)',
            'total_tax'       => 'SUM(IFNULL(ei.tax_amount, 0) * e.store_to_base_rate * e.base_to_global_rate)',
            'total_discount'  => 'SUM(ABS(IFNULL(ei.discount_amount, 0)) * e.store_to_base_rate * e.base_to_global_rate)',
            'total_revenue'   => 'SUM((ei.row_total + IFNULL(ei.tax_amount, 0) - ABS(IFNULL(ei.discount_amount, 0))) * e.store_to_base_rate * e.base_to_global_rate)'
        );
    }

    public function getShippingFilter()
    {
        return '(e.shipping_amount + IFNULL(e.shipping_tax_amount, 0)) > 0';
    }

    public function getPaymentMethodCodes($type)
    {
        if (isset($this->_paymentTypeMap[$type])) {
            return $this->_paymentTypeMap[$type];
        }

        return array('');
    }

    /**
     * Returns an array of columns to additionally select for reconcile reports
     *
     * @param Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract $collection
     * @return array
     */
    public function getAdditionalReconcileColumns($collection)
    {
        $cols = array();

        if (!$collection->isTotals() && !$collection->isSubTotals()) {
            $cols['order_num'] = 'o.increment_id';
        }

        return $cols;
    }

    /**
     * Returns an array of columns to additionally select for cost object reports
     *
     * @param Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract $collection
     * @return array
     */
    public function getAdditionalCostObjectColumns($collection, $fromReconcile = false)
    {
        $cols = array();
        if (!$fromReconcile) {
            $cols = $this->getAdditionalReconcileColumns($collection);
        }

        if (!$collection->isTotals() && !$collection->isSubTotals()) {
            $cols['po_number'] = 'p.po_number';
        }

        return $cols;
    }

    /**
     * Joins the order billing name to a bursar report, if not a total
     *
     * @param Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract $collection
     */
    public function joinBillingNameToCollection($collection)
    {
        if (!$collection->isTotals() && !$collection->isSubTotals()) {
            $adapter       = $collection->getConnection();
            $ifnullFirst   = $adapter->getIfNullSql('oa.firstname', $adapter->quote(''));
            $ifnullLast    = $adapter->getIfNullSql('oa.lastname', $adapter->quote(''));
            $concatAddress = $adapter->getConcatSql(array($ifnullFirst, $adapter->quote(' '), $ifnullLast));

            $collection->getSelect()
                ->joinLeft(array('oa' => $collection->getTable('sales/order_address')),
                    "(e.order_id = oa.parent_id AND oa.address_type = 'billing')",
                    array('billing_name' => $concatAddress)
                );
        }
    }

    /**
     * @param Unl_Core_Model_Resource_Report_Bursar_Collection_Abstract $collection
     */
    public function addStoreFilterToCollection($collection, $storeIds)
    {
        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        if (!empty($storeIds) && $storeIds[0] != 0) {
            $adapter = $collection->getConnection();
            $collection->getSelect()->where($adapter->prepareSqlCondition('oi.source_store_view', array('in' => $storeIds)));
        }
    }

    /**
     * @param Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract $grid
     * @param boolean $isShipping
     */
    public function addReconcileColumns($grid, $isShipping = false)
    {
        $after = $isShipping ? 'period' : 'merchant';
        $subtotal = $isShipping ? 'SubTotal' : '';

        $grid->addColumnAfter('order_num', array(
            'header'          => Mage::helper('sales')->__('Order #'),
            'index'           => 'order_num',
            'totals_label'    => '',
            'subtotals_label' => $subtotal,
            'sortable'        => false
        ), $after);
    }

    /**
     * @param Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract $grid
     * @param boolean $isShipping
     */
    public function addCostObjectColumns($grid, $fromReconcile = false, $isShipping = false)
    {
        $after = $isShipping ? 'period' : 'merchant';

        if (!$fromReconcile) {
            $this->addReconcileColumns($grid, $isShipping);
        }

        $grid->addColumnAfter('po_number', array(
            'header'          => Mage::helper('sales')->__('Cost Object'),
            'index'           => 'po_number',
            'totals_label'    => '',
            'subtotals_label' => '',
            'sortable'        => false
        ), $after);

        $grid->addColumnAfter('billing_name', array(
            'header'          => Mage::helper('sales')->__('Billing Name'),
            'index'           => 'billing_name',
            'totals_label'    => '',
            'subtotals_label' => '',
            'sortable'        => false
        ), 'po_number');
    }
}
