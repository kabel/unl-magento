<?php

class Unl_Core_Model_Resource_Report_Bursar_Collection_Products_Refunded extends Unl_Core_Model_Resource_Report_Bursar_Collection_Refunded
{
    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        $this->_selectedColumns += Mage::helper('unl_core/report_bursar')->getItemAggregateColumns();

        return $this->_selectedColumns;
    }

    protected  function _initSelect()
    {
        return $this->_initSelectForProducts();
    }
}
