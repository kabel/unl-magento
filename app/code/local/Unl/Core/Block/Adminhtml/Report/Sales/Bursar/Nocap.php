<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Nocap extends Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_bursar_nocap';
        $this->_headerText = Mage::helper('reports')->__('Bursar Report: Non-Captured');
        parent::__construct();
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/nocap', array('_current' => true));
    }
}