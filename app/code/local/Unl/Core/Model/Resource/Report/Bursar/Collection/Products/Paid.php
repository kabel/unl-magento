<?php

class Unl_Core_Model_Resource_Report_Bursar_Collection_Products_Paid extends Unl_Core_Model_Resource_Report_Bursar_Collection_Paid
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
