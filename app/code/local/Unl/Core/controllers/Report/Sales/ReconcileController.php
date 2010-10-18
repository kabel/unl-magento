<?php

class Unl_Core_Report_Sales_ReconcileController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Sales'), Mage::helper('reports')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Reconcile'), Mage::helper('reports')->__('Reconcile'));
        return $this;
    }
    
    public function _initReportAction($blocks)
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

    public function ccAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Credit Card'));
        
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Credit Card'), Mage::helper('adminhtml')->__('Credit Card'));
        
        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_sales_reconcile_cc.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
            
        $this->renderLayout();
    }
    
    /**
     * Export reconcile report grid to CSV format
     */
    public function exportCcCsvAction()
    {
        $fileName   = 'reconcile_cc.csv';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_cc_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportCcExcelAction()
    {
        $fileName   = 'reconcile_cc.xml';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_cc_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }
    
    public function coAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Cost Object'));
        
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Cost Object'), Mage::helper('adminhtml')->__('Cost Object'));
        
        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_sales_reconcile_co.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
            
        $this->renderLayout();
    }
    
    /**
     * Export reconcile report grid to CSV format
     */
    public function exportCoCsvAction()
    {
        $fileName   = 'reconcile_co.csv';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_co_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportCoExcelAction()
    {
        $fileName   = 'reconcile_co.xml';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_co_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }
    
    public function nocapAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Reconcile'))->_title($this->__('Non-Captured'));
        
        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Non-Captured'), Mage::helper('adminhtml')->__('Non-Captured'));
        
        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_sales_reconcile_nocap.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));
            
        $this->renderLayout();
    }
    
    /**
     * Export reconcile report grid to CSV format
     */
    public function exportNocapCsvAction()
    {
        $fileName   = 'reconcile_nocap.csv';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_nocap_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    /**
     * Export sales report grid to Excel XML format
     */
    public function exportNocapExcelAction()
    {
        $fileName   = 'reconcile_nocap.xml';
        $grid       = $this->getLayout()->createBlock('unl_core/adminhtml_report_sales_reconcile_nocap_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
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
            case 'exportCcCsv':
            case 'exportCcExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/cc');
                break;
            case 'exportCoCsv':
            case 'exportCoExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/co');
                break;
            case 'exportNocapCsv':
            case 'exportNocapExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot/reconcile/nocap');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/salesroot');
                break;
        }
    }
}