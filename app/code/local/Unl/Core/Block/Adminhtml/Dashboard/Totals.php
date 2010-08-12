<?php

class Unl_Core_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Totals
{
    protected function _prepareLayout()
    {
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $websiteScope = ($this->getRequest()->getParam('website') !== null);
        $period = $this->getRequest()->getParam('period', '24h');
        
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addCreateAtPeriodFilter($period);
        
        $storeIds = array();
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }
        
        $collection->calculateTotals($isFilter, $websiteScope, $storeIds);

        $collection->load();
        
        $totals = $collection->getFirstItem();

        $this->addTotal($this->__('Revenue'), $totals->getRevenue());
        $this->addTotal($this->__('Tax'), $totals->getTax());
        if (!$isFilter || $this->getRequest()->getParam('website')) {
            $this->addTotal($this->__('Shipping'), $totals->getShipping());
        }
        $this->addTotal($this->__('Quantity'), $totals->getQuantity()*1, true);
    }
}