<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Co extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_bursar_co';
        $this->_headerText = Mage::helper('reports')->__('Bursar Report: Cost Object');
        parent::__construct();
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/co', array('_current' => true));
    }
}