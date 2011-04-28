<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Cc
    extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_bursar_cc';
        $this->_headerText = Mage::helper('reports')->__('Bursar Report: Credit Card');
        parent::__construct();
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/cc', array('_current' => true));
    }
}
