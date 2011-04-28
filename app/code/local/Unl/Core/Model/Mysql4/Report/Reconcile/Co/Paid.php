<?php

class Unl_Core_Model_Mysql4_Report_Reconcile_Co_Paid extends Unl_Core_Model_Mysql4_Report_Reconcile_Collection_Paid
{
    protected $_paymentMethodCodes = array('purchaseorder');

    protected function _getSelectedColumns()
    {
        parent::_getSelectedColumns();
        if (!$this->isTotals() && !$this->isSubTotals()) {
            $this->_selectedColumns += array('po_number' => 'p.po_number');
        }

        return $this->_selectedColumns;
    }
}
