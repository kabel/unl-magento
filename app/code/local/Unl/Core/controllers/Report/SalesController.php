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
    
    /**
     * Export bursar report grid to CSV format
     */
    public function exportBursarCsvAction()
    {
        $fileName   = 'bursar.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportBursarExcelAction()
    {
        $fileName   = 'bursar.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
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