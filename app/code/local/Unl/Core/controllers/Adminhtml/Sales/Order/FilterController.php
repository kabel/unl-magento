<?php

class Unl_Core_Adminhtml_Sales_Order_FilterController extends Unl_Core_Controller_Advfilter
{
    public function applyAction()
    {
        $sessionParamName = Mage::helper('unl_core')->getAdvancedGridFiltersStorageKey('order');
        $this->_applyFilters($sessionParamName);
    }

    public function currentAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('unl_core/adminhtml_sales_order_grid_filter_form');
        $resp = $this->_getFilterFromBlock($block);

        $this->getResponse()->setBody(Zend_Json::encode($resp));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }
}
