<?php

class Unl_Core_Report_ProductController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $act = $this->getRequest()->getActionName();
        if(!$act)
            $act = 'default';
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
}