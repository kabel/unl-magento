<?php

class Unl_Core_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Totals
{
    protected function _prepareLayout()
    {
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');

        $collection = Mage::getResourceModel('reports/order_collection');
        
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
            $websiteScope = 0;
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $websiteScope = 1;
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $websiteScope = 0;
        }
        
        if ($isFilter) {
            $collection->calculateTotals($isFilter, $websiteScope, $storeIds);
        } else {
            $collection->calculateTotals($isFilter);
        }

        $collection->load();
        $collectionArray = $collection->toArray();
        $totals = array_pop($collectionArray);

        $this->addTotal($this->__('Revenue'), $totals['revenue']);
        $this->addTotal($this->__('Tax'), $totals['tax']);
        if (!$isFilter || $this->getRequest()->getParam('website')) {
            $this->addTotal($this->__('Shipping'), $totals['shipping']);
        }
        $this->addTotal($this->__('Quantity'), $totals['quantity']*1, true);
    }
}