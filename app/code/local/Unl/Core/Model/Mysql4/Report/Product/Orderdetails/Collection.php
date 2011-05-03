<?php

class Unl_Core_Model_Mysql4_Report_Product_Orderdetails_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_periodColumn;

	/**
     * Initialize custom resource model
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('sales/order_item', 'item_id');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Add selected data
     *
     * @return Mage_Sales_Model_Mysql4_Report_Order_Collection
     */
    protected function _initSelect()
    {
        $compositeTypeIds = Mage::getSingleton('catalog/product_type')->getCompositeTypes();
        $productTypes = $this->getConnection()->quoteInto(' AND (main_table.product_type NOT IN (?))', $compositeTypeIds);

        $_joinCondition = $this->getConnection()->quoteInto(
            'order.entity_id = main_table.order_id AND order.state<>?', Mage_Sales_Model_Order::STATE_CANCELED
        );
        $_joinCondition .= $productTypes;

        $this->_setPeriodFormat();
        $this->getSelect()->from(array('main_table' => $this->getResource()->getMainTable()), array('*', 'period' => $this->_periodFormat))
            ->joinInner(
                array('order' => $this->getTable('sales/order')),
                $_joinCondition,
                array('ordernum' => 'increment_id')
            )
            ->joinInner(
                array('_table_billing_address' => $this->getTable('sales/order_address')),
                "order.entity_id = _table_billing_address.parent_id AND _table_billing_address.address_type = 'billing'",
                array(
                	'customer_firstname' => new Zend_Db_Expr('CASE WHEN order.customer_id IS NULL THEN _table_billing_address.firstname ELSE order.customer_firstname END'),
                	'customer_lastname' => new Zend_Db_Expr('CASE WHEN order.customer_id IS NULL THEN _table_billing_address.lastname ELSE order.customer_lastname END')
                )
            );

        return $this;
    }

    protected function _setPeriodFormat()
    {
        if ('month' == $this->_period) {
            $this->_periodFormat = "DATE_FORMAT(DATE({$this->_getPeriodColumn()}), '%Y-%m')";
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = "EXTRACT(YEAR FROM DATE({$this->_getPeriodColumn()}))";
        } else {
            $this->_periodFormat = "DATE({$this->_getPeriodColumn()})";
        }

        return $this;
    }

    protected function _getPeriodColumn()
    {
        if (null === $this->_periodColumn) {
            $this->_periodColumn = "CONVERT_TZ(order.created_at, '+00:00', '{$this->_getStoreTimezoneUtcOffset()}')";
        }

        return $this->_periodColumn;
    }

    protected function _applyDateRangeFilter()
    {
        if (!is_null($this->_from)) {
            $this->getSelect()->where("DATE({$this->_periodColumn})  >= ?", $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->getSelect()->where("DATE({$this->_periodColumn}) <= ?", $this->_to);
        }
        return $this;
    }

    protected function _applyStoresFilter()
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $this->getSelect()->where('main_table.source_store_view IN(?) OR main_table.source_store_view IS NULL', $storeIds);
        } elseif ($storeIds[0] != '') {
            $this->getSelect()->where('main_table.source_store_view IN(?)', $storeIds);
        }

        return $this;
    }

    public function addSkuFilter($sku)
    {
        $this->getSelect()->where('main_table.sku LIKE ?', $sku . '%');

        return $this;
    }

	/**
     * Retrieve store timezone offset from UTC in the form acceptable by SQL's CONVERT_TZ()
     *
     * @return string
     */
    protected function _getStoreTimezoneUtcOffset($store = null)
    {
        return Mage::app()->getLocale()->storeDate($store)->toString(Zend_Date::GMT_DIFF_SEP);
    }
}
