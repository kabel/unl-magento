<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Collection_Abstract extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    protected $_periodFormat;
    protected $_periodColumn;
    protected $_selectedColumns = array();
    protected $_paymentMethodCodes = array();

    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
    }

    protected function _applyStoresFilter()
    {
        return $this;
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