<?php

require_once 'Mage/Adminhtml/controllers/Report/ProductController.php';

class Unl_Core_Adminhtml_Report_ProductController extends Mage_Adminhtml_Report_ProductController
{
    protected function _initProduct()
    {
        $productId  = (int) $this->getRequest()->getParam('id');
        $product = Mage::getModel('catalog/product')->load($productId);

        Mage::register('current_product', $product);

        return $product;
    }

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

    public function optionsPickerAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('optionsPickerGrid');
            return;
        }

        $this->_title($this->__('Reports'))
            ->_title($this->__('Products'))
            ->_title($this->__('With Options'));

        $this->_initAction()
            ->_setActiveMenu('report/product/optionsPicker');

        $this->renderLayout();
    }

    public function optionsPickerGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_optionsPicker_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function optionsAction()
    {
        $product = $this->_initProduct();

        if (!$product->getId() || !Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
            $this->_getSession()->addError(Mage::helper('unl_core')->__('Cannot load requested product'));
            $this->_redirect('*/*/optionsPicker');
            return;
        }

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('optionsGrid');
            return;
        }

        $this->_title($this->__('Reports'))
            ->_title($this->__('Products'))
            ->_title($this->__('Ordered with Options'))
            ->_title($product->getName());

        $this->_initAction()
            ->_setActiveMenu('report/product/optionsPicker');

        $this->renderLayout();
    }

    public function optionsGridAction()
    {
        $product = $this->_initProduct();

        if (!$product->getId() || !Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
            $this->_forward('denied');
            return;
        }

        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_options_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportOptionsCsvAction()
    {
        $product = $this->_initProduct();

        if (!$product->getId() || !Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
            $this->_getSession()->addError(Mage::helper('unl_core')->__('Cannot load requested product'));
            $this->_redirect('*/*/optionsPicker');
            return;
        }

        $this->_exportCsv('', 'options');
    }

    public function exportOptionsExcelAction()
    {
        $product = $this->_initProduct();

        if (!$product->getId() || !Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
            $this->_getSession()->addError(Mage::helper('unl_core')->__('Cannot load requested product'));
            $this->_redirect('*/*/optionsPicker');
            return;
        }

        $this->_exportExcel('', 'options');
    }

    protected function _isAllowed()
    {
        $session = Mage::getSingleton('admin/session');
        $act = strtolower($this->getRequest()->getActionName());

        switch ($act) {
            case 'orderdetails':
            case 'customized':
            case 'reconcile':
                return $session->isAllowed('report/products/' . $act);
                break;
            case 'optionspicker':
            case 'optionspickergrid':
            case 'options':
            case 'optionsgrid':
            case 'exportoptionscsv':
            case 'exportoptionsexcel':
                return $session->isAllowed('report/products/ordered_options');
                break;
            default:
                return parent::_isAllowed();
                break;
        }
    }
}
