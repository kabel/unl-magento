<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Cc
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
{
    public function __construct()
    {
        $this->_controller = 'cc';
        $this->_blockTitle = 'Credit Card';
        parent::__construct();
    }
}
