<?php

class Unl_Inventory_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Inventory'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/inventory');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_inventory/products_grid')->toHtml()
        );
    }

    protected function _initProduct()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Inventory'));

        $productId  = (int) $this->getRequest()->getParam('id');
        if (!$productId) {
            return null;
        }
        $product = Mage::getModel('catalog/product')->load($productId);

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        return $product;
    }

    public function editAction()
    {
        if (!$product = $this->_initProduct()) {
            return $this->_redirect('*/*/');
        }

        $this->loadLayout();
        $this->_title($product->getName());
        $this->_setActiveMenu('catalog/inventory');
        $this->renderLayout();
    }

    public function saveAction()
    {

    }

    public function auditGridAction()
    {
        $this->_initProduct();
        $this->getResponse()->setBody($this->getLayout()->createBlock('unl_inventory/inventory_edit_tab_audit')->toHtml());
    }

    public function exportAuditCsvAction()
    {

    }

    public function exportAuditExcelAction()
    {

    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('calalog/inventory');
    }
}
