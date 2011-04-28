<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Co
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_reconcile_co';
        $this->_headerText = Mage::helper('reports')->__('Reconcile Report: Cost Object');
        parent::__construct();
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/co', array('_current' => true));
    }
}
