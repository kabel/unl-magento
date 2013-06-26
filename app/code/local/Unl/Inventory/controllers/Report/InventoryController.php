<?php

class Unl_Inventory_Report_InventoryController extends Mage_Adminhtml_Controller_Action
{
    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Reports'), Mage::helper('adminhtml')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Products'), Mage::helper('adminhtml')->__('Products'));
        return $this;
    }

    public function valuationAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('valuationGrid');
            return;
        }

        $this->_title($this->__('Reports'))
            ->_title($this->__('Products'))
            ->_title($this->__('Inventory Valuation'));

        $this->_initAction()
            ->_setActiveMenu('report/products/inventory_valuation')
            ->_addBreadcrumb(Mage::helper('unl_inventory')->__('Inventory Valuation'),
                Mage::helper('unl_inventory')->__('Invengtory Valuation'))
            ->renderLayout();
    }

    public function valuationGridAction()
    {
        $this->loadLayout()
            ->renderLayout();
    }

    public function exportValuationExcelAction()
    {
        $fileName   = 'valuation.xml';
        $content    = $this->getLayout()->createBlock('unl_inventory/report_valuation_grid')
            ->getExcelFile($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportValuationCsvAction()
    {
        $fileName   = 'valuation.csv';
        $content    = $this->getLayout()->createBlock('unl_inventory/report_valuation_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/products/inventory_valuation');
    }
}
