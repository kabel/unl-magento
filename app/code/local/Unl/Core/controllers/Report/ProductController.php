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

    public function orderdetailsAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Order Details'));

        $this->_initAction()
            ->_setActiveMenu('report/product/orderdetails')
            ->_addBreadcrumb(Mage::helper('reports')->__('Order Details'), Mage::helper('reports')->__('Order Details'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_product_orderdetails.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function exportOrderdetailsCsvAction()
    {
        $fileName = 'products_orderdetails.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOrderdetailsExcelAction()
    {
        $fileName = 'products_orderdetails.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_orderdetails_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    public function customizedAction()
    {
        $this->_title($this->__('Reports'))
             ->_title($this->__('Products'))
             ->_title($this->__('Customized'));

        $this->_initAction()
            ->_setActiveMenu('report/product/customized')
            ->_addBreadcrumb(Mage::helper('reports')->__('Customized'), Mage::helper('reports')->__('Customized'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_product_customized.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function exportCustomizedCsvAction()
    {
        $fileName   = 'products_customized.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportCustomizedExcelAction()
    {
        $fileName   = 'products_customized.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_product_customized_grid');
        $this->_initReportAction($grid);
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
            case 'exportOrderdetailsCsv':
            case 'exportOrderdetailsExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/products/orderdetails');
                break;
            case 'exportCustomizedCsv':
            case 'exportCustomizedExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/products/customized');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/products');
                break;
        }
    }
}
