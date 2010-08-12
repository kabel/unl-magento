<?php

class Unl_Core_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales
{
    protected function _prepareLayout()
    {
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $websiteScope = ($this->getRequest()->getParam('website') !== null);
        
        $storeIds = array();
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        
        $collection = Mage::getResourceModel('reports/order_collection')
            ->calculateSales($isFilter, $websiteScope, $storeIds);
        
        $collection->load();
        $sales = $collection->getFirstItem();

        $this->addTotal($this->__('Lifetime Sales'), $sales->getLifetime());
        $this->addTotal($this->__('Average Orders'), $sales->getAverage());
    }
}