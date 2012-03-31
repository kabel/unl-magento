<?php

class Unl_Core_Model_Resource_Report_Tax_Reconcile_Refunded extends Unl_Core_Model_Resource_Report_Tax_Reconcile_Paid
{
    protected function _getInnerMainTable()
    {
        return $this->getTable('sales/creditmemo');
    }

    protected function _getInnerDateColumn()
    {
        return 'refunded_at';
    }
}
