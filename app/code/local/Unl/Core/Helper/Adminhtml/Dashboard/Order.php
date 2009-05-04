<?php

class Unl_Core_Helper_Adminhtml_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Order
{
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');
            
        if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
            $websiteScope = 0;
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $websiteScope = 1;
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $websiteScope = 0;
        }
        
        if ($isFilter) {
            $this->_collection = Mage::getResourceSingleton('reports/order_collection')
                ->prepareSummary($this->getParam('period'), 0, 0, $isFilter, $websiteScope, $storeIds);
        } else {
            $this->_collection = Mage::getResourceSingleton('reports/order_collection')
                ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);
        }

        $this->_collection->load();
    }
}