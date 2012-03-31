<?php

require_once 'Mage/Adminhtml/controllers/Report/ProductController.php';

class Unl_Core_Adminhtml_Report_ProductController extends Mage_Adminhtml_Report_ProductController
{
    public function orderdetailsAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('orderdetailsGrid');
            return;
        }

        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Order Details'));

        $this->_initAction()
            ->_setActiveMenu('report/product/orderdetails')
            ->_addBreadcrumb(Mage::helper('reports')->__('Order Details'), Mage::helper('reports')->__('Order Details'));

        $this->renderLayout();
    }

    public function orderdetailsGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportOrderdetailsCsvAction()
    {
        $fileName = 'products_orderdetails.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOrderdetailsExcelAction()
    {
        $fileName = 'products_orderdetails.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    public function customizedAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('customizedGrid');
            return;
        }

        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Customized'));

        $this->_initAction()
            ->_setActiveMenu('report/product/customized')
            ->_addBreadcrumb(Mage::helper('reports')->__('Customized'), Mage::helper('reports')->__('Customized'));

        $this->renderLayout();
    }

    public function customizedGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportCustomizedCsvAction()
    {
        $fileName   = 'products_customized.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportCustomizedExcelAction()
    {
        $fileName   = 'products_customized.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'orderdetails':
            case 'customized':
                return Mage::getSingleton('admin/session')->isAllowed('report/products/' . $act);
                break;
            default:
                return parent::_isAllowed();
                break;
        }
    }
}
