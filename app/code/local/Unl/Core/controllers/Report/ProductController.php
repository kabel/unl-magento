<?php

class Unl_Core_Report_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Products'), Mage::helper('reports')->__('Products'));
        return $this;
    }
    
    public function orderdetailsAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Products Ordered'));
             
        $this->_initAction()
            ->_setActiveMenu('report/product/orderdetails')
            ->_addBreadcrumb(Mage::helper('reports')->__('Order Details'), Mage::helper('reports')->__('Order Details'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails'))
            ->renderLayout();
    }
    
    public function exportOrderdetailsCsvAction()
    {
        $fileName   = 'products_orderdetails.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid')
            ->getCsv();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function exportOrderdetailsExcelAction()
    {
        $fileName   = 'products_orderdetails.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function customizedAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Customized'));
             
        $this->_initAction()
            ->_setActiveMenu('report/product/customized')
            ->_addBreadcrumb(Mage::helper('reports')->__('Customized'), Mage::helper('reports')->__('Customized'))
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized'))
            ->renderLayout();
    }
    
    public function exportCustomizedCsvAction()
    {
        $fileName   = 'products_customized.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid')
            ->getCsv($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function exportCustomizedExcelAction()
    {
        $fileName   = 'products_customized.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'orderdetails':
            case 'customized':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/' . $act);
                break;
            case 'exportOrderdetailsCsv':
            case 'exportOrderdetailsExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/orderdetails');
                break;
            case 'exportCustomizedCsv':
            case 'exportCustomizedExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/customized');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}