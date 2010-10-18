<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Grid_Abstract extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';
    
    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
    }
}