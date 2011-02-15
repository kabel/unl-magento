<?php

abstract class Unl_Core_Controller_Advfilter extends Mage_Adminhtml_Controller_Action
{
    abstract public function applyfilterAction();

    abstract public function currentfiltersAction();

    protected function _applyFilters($sessionParam, $dateFields = null)
    {
        $session = Mage::getSingleton('adminhtml/session');
        if ($this->getRequest()->has('advfilter')) {
            $requestData = $this->getRequest()->getParam('advfilter');
            $requestData = Mage::helper('adminhtml')->prepareFilterString($requestData);

            if (!empty($dateFields)) {
                $requestData = $this->_filterDates($requestData, $dateFields);
            }

            $params = new Varien_Object();

            foreach ($requestData as $key => $value) {
                if (!empty($value)) {
                    $params->setData($key, $value);
                }
            }

            $session->setData($sessionParam, $params);
        }
    }

    protected function _getFilterFromBlock($block)
    {
        $block->toHtml();
        $filters = $block->getFilterData();
        $resp = new Varien_Object();
        foreach ($block->getForm()->getElement('base_fieldset')->getElements() as $element) {
            switch ($element->getType()) {
                case 'button':
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

        return $resp;
    }
}