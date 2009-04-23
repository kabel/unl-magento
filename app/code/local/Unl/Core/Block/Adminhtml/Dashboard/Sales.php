<?php

class Unl_Core_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales
{
    protected function _prepareLayout()
    {
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        
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
        
        $collection = Mage::getResourceModel('reports/order_collection');
        if ($isFilter) {
            $collection->calculateSales($isFilter, $websiteScope, $storeIds);
        } else {
            $collection->calculateSales($isFilter);
        }
        
        $collection->load();
        $collectionArray = $collection->toArray();
        $sales = array_pop($collectionArray);

        $this->addTotal($this->__('Lifetime Sales'), $sales['lifetime']);
        $this->addTotal($this->__('Average Orders'), $sales['average']);
    }
}