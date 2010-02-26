<?php

class Unl_Core_Report_Sales_BursarController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Bursar'), Mage::helper('reports')->__('Bursar'));
        return $this;
    }

    public function ccAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Credit Card'), Mage::helper('adminhtml')->__('Credit Card'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_cc'))
            ->renderLayout();
    }
    
    /**
     * Export bursar report grid to CSV format
     */
    public function exportCcCsvAction()
    {
        $fileName   = 'bursar_cc.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_cc_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportCcExcelAction()
    {
        $fileName   = 'bursar_cc.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_cc_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function coAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cost Object'), Mage::helper('adminhtml')->__('Cost Object'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_co'))
            ->renderLayout();
    }
    
    /**
     * Export bursar report grid to CSV format
     */
    public function exportCoCsvAction()
    {
        $fileName   = 'bursar_co.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_co_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportCoExcelAction()
    {
        $fileName   = 'bursar_co.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_co_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function nocapAction()
    {
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Non-Captured'), Mage::helper('adminhtml')->__('Non-Captured'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_nocap'))
            ->renderLayout();
    }
    
    /**
     * Export bursar report grid to CSV format
     */
    public function exportNocapCsvAction()
    {
        $fileName   = 'bursar_nocap.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_nocap_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportNocapExcelAction()
    {
        $fileName   = 'bursar_nocap.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_bursar_nocap_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'cc':
            case 'co':
            case 'nocap':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/bursar');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}