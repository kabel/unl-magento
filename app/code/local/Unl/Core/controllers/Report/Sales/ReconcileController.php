<?php

class Unl_Core_Report_Sales_ReconcileController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Reconcile'), Mage::helper('reports')->__('Reconcile'));
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

    protected function _exportCsv($gridId, $paymentGroup)
    {
        $fileName   = "reconcile_{$paymentGroup}_{$gridId}.csv";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_reconcile_{$paymentGroup}_{$gridId}");
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    protected function _exportExcel($gridId, $paymentGroup)
    {
        $fileName   = "reconcile_{$paymentGroup}_{$gridId}.xml";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_reconcile_{$paymentGroup}_{$gridId}");
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    public function ccAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Credit Card'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Credit Card'), Mage::helper('adminhtml')->__('Credit Card'));

        $this->_initActionBlocks();
    }


    public function exportCsvCcPaidAction()
    {
        $this->_exportCsv('paid', 'cc');
    }

    public function exportCsvCcRefundedAction()
    {
        $this->_exportCsv('refunded', 'cc');
    }

    public function exportExcelCcPaidAction()
    {
        $this->_exportExcel('paid', 'cc');
    }

    public function exportExcelCcRefundedAction()
    {
        $this->_exportExcel('refunded', 'cc');
    }

    public function coAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Cost Object'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cost Object'), Mage::helper('adminhtml')->__('Cost Object'));

        $this->_initActionBlocks();
    }

    public function exportCsvCoPaidAction()
    {
        $this->_exportCsv('paid', 'co');
    }

    public function exportCsvCoRefundedAction()
    {
        $this->_exportCsv('refunded', 'co');
    }

    public function exportExcelCoPaidAction()
    {
        $this->_exportExcel('paid', 'co');
    }

    public function exportExcelCoRefundedAction()
    {
        $this->_exportExcel('refunded', 'co');
    }

    public function nocapAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Non-Captured'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Non-Captured'), Mage::helper('adminhtml')->__('Non-Captured'));

        $this->_initActionBlocks();
    }

    public function exportCsvNocapPaidAction()
    {
        $this->_exportCsv('paid', 'nocap');
    }

    public function exportCsvNocapRefundedAction()
    {
        $this->_exportCsv('refunded', 'nocap');
    }

    public function exportExcelNocapPaidAction()
    {
        $this->_exportExcel('paid', 'nocap');
    }

    public function exportExcelNocapRefundedAction()
    {
        $this->_exportExcel('refunded', 'nocap');
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'cc':
            case 'co':
            case 'nocap':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/' . $act);
                break;
            case 'exportCsvCcPaid':
            case 'exportCsvCcRefunded':
            case 'exportExcelCcPaid':
            case 'exportExcelCcRefunded':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/cc');
                break;
            case 'exportCsvCoPaid':
            case 'exportCsvCoRefunded':
            case 'exportExcelCoPaid':
            case 'exportExcelCoRefunded':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/co');
                break;
            case 'exportCsvNocapPaid':
            case 'exportCsvNocapRefunded':
            case 'exportExcelNocapPaid':
            case 'exportExcelNocapRefunded':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/nocap');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}
