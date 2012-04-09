<?php

class Unl_CustomerTag_CustomerTag_CategoryController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCategory()
    {

        $model = Mage::getModel('catalog/category');
        Mage::register('current_category', $model);
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
        $category = $this->_initCategory();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_customertag/category_tab_access')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/categories');
    }
}
