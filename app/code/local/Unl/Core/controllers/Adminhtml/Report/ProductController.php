<?php

require_once 'Mage/Adminhtml/controllers/Report/ProductController.php';

class Unl_Core_Adminhtml_Report_ProductController extends Mage_Adminhtml_Report_ProductController
{
    public function reconcileAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Products'))
            ->_title($this->__('Reconcile'));

        $this->_initAction()
            ->_setActiveMenu('report/product/reconcile')
            ->_addBreadcrumb(Mage::helper('reports')->__('Reconcile'), Mage::helper('reports')->__('Reconcile'));

        $this->renderLayout();
    }

    public function reconcilePaidGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_reconcile_paid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function reconcileRefundedGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_reconcile_refunded')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportReconcilePaidCsvAction()
    {
        $this->_exportCsv('paid', 'reconcile');
    }

    public function exportReconcilePaidExcelAction()
    {
        $this->_exportExcel('paid', 'reconcile');
    }

    public function exportReconcileRefundedCsvAction()
    {
        $this->_exportCsv('refunded', 'reconcile');
    }

    public function exportReconcileRefundedExcelAction()
    {
        $this->_exportExcel('refunded', 'reconcile');
    }

    protected function _exportCsv($gridId, $action)
    {
        $fileName = "products_{$action}";
        if (empty($gridId)) {
            $gridId = 'grid';
        } else {
            $fileName .= "_{$gridId}";
        }
        $fileName .= '.csv';
        $grid = $this->getLayout()->createBlock("unl_core/adminhtml_report_product_{$action}_{$gridId}");
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    protected function _exportExcel($gridId, $action)
    {
        $fileName = "products_{$action}";
        if (empty($gridId)) {
            $gridId = 'grid';
        } else {
            $fileName .= "_{$gridId}";
        }
        $fileName .= '.xml';
        $grid = $this->getLayout()->createBlock("unl_core/adminhtml_report_product_{$action}_{$gridId}");
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

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
        $this->_exportCsv('', 'orderdetails');
    }

    public function exportOrderdetailsExcelAction()
    {
        $this->_exportExcel('', 'orderdetails');
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
        $this->_exportCsv('', 'customized');
    }

    public function exportCustomizedExcelAction()
    {
        $this->_exportExcel('', 'customized');
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'orderdetails':
            case 'customized':
            case 'reconcile':
                return Mage::getSingleton('admin/session')->isAllowed('report/products/' . $act);
                break;
            default:
                return parent::_isAllowed();
                break;
        }
    }
}
