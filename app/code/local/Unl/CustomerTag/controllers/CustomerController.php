<?php

class Unl_CustomerTag_CustomerController extends Mage_Adminhtml_Controller_Action
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
        $customer = $this->_initCustomer();
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_customertag/customer_edit_tab_tag')
                ->setCustomerId($customer->getId())
                ->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
