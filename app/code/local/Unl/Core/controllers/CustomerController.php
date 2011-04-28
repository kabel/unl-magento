<?php

class Unl_Core_CustomerController extends Unl_Core_Controller_Advfilter
{
    public function applyfilterAction()
    {
        $sessionParamName = Mage::helper('unl_core')->getAdvancedGridFiltersStorageKey('customer');
        $this->_applyFilters($sessionParamName, array('purchase_from', 'purchase_to'));
    }

    public function currentfiltersAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('unl_core/adminhtml_customer_grid_filter_form');
        $resp = $this->_getFilterFromBlock($block);

        $this->getResponse()->setBody(Zend_Json::encode($resp));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
