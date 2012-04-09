<?php

class Unl_CustomerTag_CustomerTag_ProductController extends Mage_Adminhtml_Controller_Action
{
    protected function _initProduct()
    {

        $model = Mage::getModel('catalog/product');
        Mage::register('current_product', $model);
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $model->load($id);

            if (!$model->getId()) {
                return false;
            }
        }

        return $model;
    }

    public function gridAction()
    {
        $product = $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridOnlyAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/products');
    }
}
