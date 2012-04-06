<?php

class Unl_Core_Adminhtml_Sales_Order_FilterController extends Unl_Core_Controller_Advfilter
{
    public function applyAction()
    {
        $this->_applyFilters('order');
    }

    public function currentAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('unl_core/adminhtml_sales_order_grid_filter_form');
        $resp = $this->_getFilterFromBlock($block);

        $this->getResponse()->setBody(Zend_Json::encode($resp));
    }

    public function freezeAction()
    {
        $this->_freezeFilters('order');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}
