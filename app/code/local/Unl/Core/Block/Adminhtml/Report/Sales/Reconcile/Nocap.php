<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Nocap
    extends Unl_Core_Block_Adminhtml_Report_Sales_Reconcile_Abstract
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_report_sales_reconcile_nocap';
        $this->_headerText = Mage::helper('reports')->__('Reconcile Report: Non-Captured');
        parent::__construct();
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/nocap', array('_current' => true));
    }
}
