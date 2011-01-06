<?php

class Unl_Core_CustomerController extends Mage_Adminhtml_Controller_Action
{
    public function applyfilterAction()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $sessionParamName = 'customerGridadvfilter';
        if ($this->getRequest()->has('advfilter')) {
            $requestData = $this->getRequest()->getParam('advfilter');
            $requestData = Mage::helper('adminhtml')->prepareFilterString($requestData);
            $requestData = $this->_filterDates($requestData, array('purchase_from', 'purchase_to'));

            $params = new Varien_Object();

            foreach ($requestData as $key => $value) {
                if (!empty($value)) {
                    $params->setData($key, $value);
                }
            }

            $session->setData($sessionParamName, $params);
        }
    }

    public function currentfiltersAction()
    {
        $this->loadLayout();

        $block = $this->getLayout()->createBlock('unl_core/adminhtml_customer_grid_filter_form');
        $block->toHtml();
        $resp = new Varien_Object();
        foreach ($block->getForm()->getElement('base_fieldset')->getElements() as $element) {
            switch ($element->getType()) {
                case 'button':
                    break;
                case 'checkbox':
                    if ($element->getIsChecked()) {
                        $resp->setData($element->getHtmlId(), $element->getValue());
                    }
                    break;
                default:
                    if ($element->getValue()) {
                        $resp->setData($element->getHtmlId(), $element->getValue());
                    }
            }
        }

        if (!$resp->hasData()) {
            $resp = new stdClass();
        }

        $this->getResponse()->setBody(Zend_Json::encode($resp));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}