<?php

class Unl_CustomerTag_CustomerTag_CustomerController extends Mage_Adminhtml_Controller_Action
{
    protected function _initCustomer()
    {

        $model = Mage::getModel('customer/customer');
        Mage::register('current_customer', $model);
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
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function gridOnlyAction()
    {
        $this->_initCustomer();
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
