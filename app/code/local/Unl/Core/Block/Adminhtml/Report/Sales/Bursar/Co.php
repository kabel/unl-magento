<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
{
    public function __construct()
    {
        $this->_controller = 'co';
        $this->_blockTitle = 'Cost Object';
        parent::__construct();
    }
}
