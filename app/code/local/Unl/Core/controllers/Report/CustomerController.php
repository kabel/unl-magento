<?php

class Unl_Core_Report_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('reports')->__('Products'), Mage::helper('reports')->__('Customers'));
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

    public function orderaddressAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Customers'))
            ->_title($this->__('Order Address'));

        $this->_initAction()
            ->_setActiveMenu('report/customer/orderaddress')
            ->_addBreadcrumb(Mage::helper('reports')->__('Order Address'), Mage::helper('reports')->__('Order Address'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_report_customer_orderaddress.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function exportOrderaddressCsvAction()
    {
        $fileName = 'customer_orderaddress.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_customer_orderaddress_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOrderaddressExcelAction()
    {
        $fileName = 'customer_orderaddress.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_customer_orderaddress_grid');
        $this->_initReportAction($grid);
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'orderaddress':
                return Mage::getSingleton('admin/session')->isAllowed('report/customer/' . $act);
                break;
            case 'exportOrderdetailsCsv':
            case 'exportOrderdetailsExcel':
                return Mage::getSingleton('admin/session')->isAllowed('report/customer/orderaddress');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('report/customer');
            break;
        }
    }
}
