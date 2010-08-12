<?php

class Unl_Core_Helper_Adminhtml_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Order
{
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');
        $websiteScope = ($this->getParam('website') !== null);
        
        $storeIds = array();
        if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
        }
        
        $this->_collection = Mage::getResourceSingleton('reports/order_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter, $websiteScope, $storeIds);

        $this->_collection->load();
    }
}