<?php

class Unl_Core_Adminhtml_Report_Sales_TaxController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Tax Reports'), Mage::helper('reports')->__('Tax Reports'));
        return $this;
    }

    protected function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        $requestData = $this->_filterDates($requestData, array('from', 'to'));
        $requestData['store_ids'] = $this->getRequest()->getParam('store_ids');
        $params = new Varien_Object();

        foreach ($requestData as $key => $value) {
            if (!empty($value)) {
                $params->setData($key, $value);
            }
        }

        foreach ($blocks as $block) {
            if ($block) {
                $block->setPeriodType($params->getData('period_type'));
                $block->setFilterData($params);
            }
        }

        return $this;
    }

    protected function _initActionBlocks()
    {
        $multiGridBlock = $this->getLayout()->getBlock('sales.report.grid.container');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $multiGridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();

        return $this;
    }

    public function totalsAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Sales'))
            ->_title($this->__('Tax Reports'))
            ->_title($this->__('Totals'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/taxroot')
            ->_addBreadcrumb(Mage::helper('reports')->__('Totals'), Mage::helper('reports')->__('Totals'));

        $this->_initActionBlocks();
    }

    public function exportTotalsPaidCsvAction()
    {
        $this->_exportCsv('paid', 'totals', true);
    }

    public function exportTotalsPaidExcelAction()
    {
        $this->_exportExcel('paid', 'totals', true);
    }

    public function exportTotalsRefundedCsvAction()
    {
        $this->_exportCsv('refunded', 'totals', true);
    }

    public function exportTotalsRefundedExcelAction()
    {
        $this->_exportExcel('refunded', 'totals', true);
    }

    public function reconcileAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Sales'))
            ->_title($this->__('Tax Reports'))
            ->_title($this->__('Reconcile'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/taxroot')
            ->_addBreadcrumb(Mage::helper('reports')->__('Reconcile'), Mage::helper('reports')->__('Reconcile'));

        $this->renderLayout();
    }

    public function reconcilePaidGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_tax_reconcile_paid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function reconcileRefundedGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_tax_reconcile_refunded')->toHtml();
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

    protected function _exportCsv($gridId, $action, $report = false)
    {
        $fileName   = "tax_{$action}_{$gridId}.csv";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_tax_{$action}_{$gridId}");
        if ($report) {
            $this->_initReportAction($grid);
        }
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    protected function _exportExcel($gridId, $action, $report = false)
    {
        $fileName   = "tax_{$action}_{$gridId}.xml";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_tax_{$action}_{$gridId}");
        if ($report) {
            $this->_initReportAction($grid);
        }
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'totals':
            case 'reconcile':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/taxroot/' . $act);
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/taxroot');
                break;
        }
    }
}
