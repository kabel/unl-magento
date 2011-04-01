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

    }

    public function saveAction()
    {

    }

    public function auditGridAction()
    {

    }


    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('calalog/inventory');
    }
}