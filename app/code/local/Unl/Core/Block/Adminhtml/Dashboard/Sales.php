<?php

class Unl_Core_Block_Adminhtml_Dashboard_Sales extends Mage_Adminhtml_Block_Dashboard_Sales
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Sales::_prepareLayout()
     * to use scoped store filters
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');

        $collection = Mage::getResourceModel('reports/order_collection')
            ->calculateSales($isFilter);

        $storeIds = null;
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);
        if (!empty($storeIds)) {
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        }

        $collection->load();
        $sales = $collection->getFirstItem();

        $this->addTotal($this->__('Lifetime Sales'), $sales->getLifetime());
        $this->addTotal($this->__('Average Orders'), $sales->getAverage());
    }
}
