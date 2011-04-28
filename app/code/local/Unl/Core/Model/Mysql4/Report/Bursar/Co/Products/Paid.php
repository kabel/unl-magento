<?php

class Unl_Core_Model_Mysql4_Report_Bursar_Co_Products_Paid extends Unl_Core_Model_Mysql4_Report_Bursar_Collection_Products_Paid
{
    protected $_paymentMethodCodes = array('purchaseorder');

    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns += array(
                'po_number' => 'p.po_number',
            	'order_num' => 'o.increment_id'
            );
        }

        return $this->_selectedColumns;
    }

    protected function _initSelect()
    {
        return $this->_initSelectForProducts(true);
    }
}
