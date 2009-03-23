<?php

class Unl_Core_Report_SalesController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'));
        return $this;
    }

    public function bursarAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Bursar Report'), Mage::helper('adminhtml')->__('Bursar Report'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar'))
            ->renderLayout();
    }
    
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'bursar':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/bursar');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}