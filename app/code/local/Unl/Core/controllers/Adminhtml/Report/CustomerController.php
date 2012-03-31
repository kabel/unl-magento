<?php

require_once 'Mage/Adminhtml/controllers/Report/CustomerController.php';

class Unl_Core_Adminhtml_Report_CustomerController extends Mage_Adminhtml_Report_CustomerController
{
    public function orderaddressAction()
    {
        $this->_title($this->__('Reports'))
            ->_title($this->__('Customers'))
            ->_title($this->__('Order Address'));

        $this->_initAction()
            ->_setActiveMenu('report/customer/orderaddress')
            ->_addBreadcrumb(Mage::helper('reports')->__('Order Address'), Mage::helper('reports')->__('Order Address'));

        $this->renderLayout();
    }

    public function orderaddressGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_customer_orderaddress_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportOrderaddressCsvAction()
    {
        $fileName = 'customer_orderaddress.csv';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_customer_orderaddress_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportOrderaddressExcelAction()
    {
        $fileName = 'customer_orderaddress.xml';
        $grid = $this->getLayout()->createBlock('unl_core/adminhtml_report_customer_orderaddress_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    protected function _isAllowed()
    {
        $act = $this->getRequest()->getActionName();
        switch ($act) {
            case 'orderaddress':
                return Mage::getSingleton('admin/session')->isAllowed('report/customers/' . $act);
                break;
            default:
                return parent::_isAllowed();
            break;
        }
    }
}
