<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
{
    public function __construct()
    {
        $this->_controller = 'co';
        $this->_blockTitle = 'Cost Object';
        parent::__construct();
    }
}
