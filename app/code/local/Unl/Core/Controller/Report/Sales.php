<?php

abstract class Unl_Core_Controller_Report_Sales extends Mage_Adminhtml_Controller_Action
{
    protected $_controllerGroup;
    protected $_controllerTitle;

    protected function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('reports')->__($this->_controllerTitle), Mage::helper('reports')->__($this->_controllerTitle));
        return $this;
    }

    protected function _initReportAction($blocks)
    {
        if (!is_array($blocks)) {
            $blocks = array($blocks);
        }

        $requestData = Mage::helper('adminhtml')->prepareFilterString($this->getRequest()->getParam('filter'));
        try {
            $requestData = $this->_filterDates($requestData, array('from', 'to'));
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            unset($requestData['from'], $requestData['to']);
        }
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
        $gridBlock  = implode('_', array($this->_controllerGroup, $paymentGroup, $gridId));
        $fileName   = "{$gridBlock}.csv";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_{$gridBlock}");
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    protected function _exportExcel($gridId, $paymentGroup)
    {
        $gridBlock  = implode('_', array($this->_controllerGroup, $paymentGroup, $gridId));
        $fileName   = "{$gridBlock}.xml";
        $grid       = $this->getLayout()->createBlock("unl_core/adminhtml_report_sales_{$gridBlock}");
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    public function ccAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Sales'))
            ->_title($this->__($this->_controllerTitle))
            ->_title($this->__('Credit Card'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Credit Card'), Mage::helper('adminhtml')->__('Credit Card'));

        $this->_initActionBlocks();
    }

    public function exportCsvCcProductsPaidAction()
    {
        $this->_exportCsv('products_paid', 'cc');
    }

    public function exportCsvCcProductsRefundedAction()
    {
        $this->_exportCsv('products_refunded', 'cc');
    }

    public function exportCsvCcShippingPaidAction()
    {
        $this->_exportCsv('shipping_paid', 'cc');
    }

    public function exportCsvCcShippingRefundedAction()
    {
        $this->_exportCsv('shipping_refunded', 'cc');
    }

    public function exportExcelCcProductsPaidAction()
    {
        $this->_exportExcel('products_paid', 'cc');
    }

    public function exportExcelCcProductsRefundedAction()
    {
        $this->_exportExcel('products_refunded', 'cc');
    }

    public function exportExcelCcShippingPaidAction()
    {
        $this->_exportExcel('shipping_paid', 'cc');
    }

    public function exportExcelCcShippingRefundedAction()
    {
        $this->_exportExcel('shipping_refunded', 'cc');
    }

    public function coAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Sales'))
            ->_title($this->__($this->_controllerTitle))
            ->_title($this->__('Cost Object'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cost Object'), Mage::helper('adminhtml')->__('Cost Object'));

        $this->_initActionBlocks();
    }

    public function exportCsvCoProductsPaidAction()
    {
        $this->_exportCsv('products_paid', 'co');
    }

    public function exportCsvCoProductsRefundedAction()
    {
        $this->_exportCsv('products_refunded', 'co');
    }

    public function exportCsvCoShippingPaidAction()
    {
        $this->_exportCsv('shipping_paid', 'co');
    }

    public function exportCsvCoShippingRefundedAction()
    {
        $this->_exportCsv('shipping_refunded', 'co');
    }

    public function exportExcelCoProductsPaidAction()
    {
        $this->_exportExcel('products_paid', 'co');
    }

    public function exportExcelCoProductsRefundedAction()
    {
        $this->_exportExcel('products_refunded', 'co');
    }

    public function exportExcelCoShippingPaidAction()
    {
        $this->_exportExcel('shipping_paid', 'co');
    }

    public function exportExcelCoShippingRefundedAction()
    {
        $this->_exportExcel('shipping_refunded', 'co');
    }

    public function nocapAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Sales'))
            ->_title($this->__($this->_controllerTitle))
            ->_title($this->__('Non-Captured'));

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Non-Captured'), Mage::helper('adminhtml')->__('Non-Captured'));

        $this->_initActionBlocks();
    }

    public function exportCsvNocapProductsPaidAction()
    {
        $this->_exportCsv('products_paid', 'nocap');
    }

    public function exportCsvNocapProductsRefundedAction()
    {
        $this->_exportCsv('products_refunded', 'nocap');
    }

    public function exportCsvNocapShippingPaidAction()
    {
        $this->_exportCsv('shipping_paid', 'nocap');
    }

    public function exportCsvNocapShippingRefundedAction()
    {
        $this->_exportCsv('shipping_refunded', 'nocap');
    }

    public function exportExcelNocapProductsPaidAction()
    {
        $this->_exportExcel('products_paid', 'nocap');
    }

    public function exportExcelNocapProductsRefundedAction()
    {
        $this->_exportExcel('products_refunded', 'nocap');
    }

    public function exportExcelNocapShippingPaidAction()
    {
        $this->_exportExcel('shipping_paid', 'nocap');
    }

    public function exportExcelNocapShippingRefundedAction()
    {
        $this->_exportExcel('shipping_refunded', 'nocap');
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        $aclPrefix = "report/salesroot/{$this->_controllerGroup}/";

        switch ($act) {
            case 'cc':
            case 'co':
            case 'nocap':
                return Mage::getSingleton('admin/session')->isAllowed($aclPrefix . $act);
                break;
            case 'exportCsvCcProductsPaid':
            case 'exportCsvCcProductsRefunded':
            case 'exportCsvCcShippingPaid':
            case 'exportCsvCcShippingRefunded':
            case 'exportExcelCcProductsPaid':
            case 'exportExcelCcProductsRefunded':
            case 'exportExcelCcShippingPaid':
            case 'exportExcelCcShippingRefunded':
                return Mage::getSingleton('admin/session')->isAllowed($aclPrefix .'cc');
                break;
            case 'exportCsvCoProductsPaid':
            case 'exportCsvCoProductsRefunded':
            case 'exportCsvCoShippingPaid':
            case 'exportCsvCoShippingRefunded':
            case 'exportExcelCoProductsPaid':
            case 'exportExcelCoProductsRefunded':
            case 'exportExcelCoShippingPaid':
            case 'exportExcelCoShippingRefunded':
                return Mage::getSingleton('admin/session')->isAllowed($aclPrefix . 'co');
                break;
            case 'exportCsvNocapProductsPaid':
            case 'exportCsvNocapProductsRefunded':
            case 'exportCsvNocapShippingPaid':
            case 'exportCsvNocapShippingRefunded':
            case 'exportExcelNocapProductsPaid':
            case 'exportExcelNocapProductsRefunded':
            case 'exportExcelNocapShippingPaid':
            case 'exportExcelNocapShippingRefunded':
                return Mage::getSingleton('admin/session')->isAllowed($aclPrefix . 'nocap');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}
