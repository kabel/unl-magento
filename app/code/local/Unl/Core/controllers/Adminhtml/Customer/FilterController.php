<?php

class Unl_Core_Adminhtml_Customer_FilterController extends Unl_Core_Controller_Advfilter
{
    public function applyAction()
    {
        $sessionParamName = Mage::helper('unl_core')->getAdvancedGridFiltersStorageKey('customer');
        $this->_applyFilters($sessionParamName, array('purchase_from', 'purchase_to'));
    }

    public function currentAction()
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
