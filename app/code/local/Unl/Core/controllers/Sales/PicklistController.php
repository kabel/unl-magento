<?php

class Unl_Core_Sales_PicklistController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('sales')->__('Sales'), Mage::helper('sales')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('sales')->__('Picklist'), Mage::helper('sales')->__('Picklist'));
        return $this;
    }

    public function indexAction()
    {
        $this->_initAction()
            ->_setActiveMenu('sales/picklist')
            ->_addContent($this->getLayout()->createBlock('unl_core/adminhtml_sales_picklist'))
            ->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName   = 'picklist.csv';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_sales_picklist_grid')
            ->getCsv($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName   = 'picklist.xml';
        $content    = $this->getLayout()->createBlock('unl_core/adminhtml_sales_picklist_grid')
            ->getExcel($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
         return Mage::getSingleton('admin/session')->isAllowed('sales/picklist');
    }
}
