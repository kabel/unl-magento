<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
{
    public function __construct()
    {
        $this->_controller = 'nocap';
        $this->_blockTitle = 'Non-Captured';
        parent::__construct();
    }
}
