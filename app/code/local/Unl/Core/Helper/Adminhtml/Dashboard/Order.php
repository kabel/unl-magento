<?php

class Unl_Core_Helper_Adminhtml_Dashboard_Order extends Mage_Adminhtml_Helper_Dashboard_Order
{
    protected function _initCollection()
    {
        $isFilter = $this->getParam('store') || $this->getParam('website') || $this->getParam('group');

        $this->_collection = Mage::getResourceSingleton('reports/order_collection')
            ->prepareSummary($this->getParam('period'), 0, 0, $isFilter);

        $storeIds = null;
        if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        if (empty($storeIds) && !$this->_collection->isLive()) {
            $this->_collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        } else if (!empty($storeIds)) {
            $this->_collection->addFieldToFilter('store_id', array('in' => implode(',', $storeIds)));
        }



        $this->_collection->load();
    }
}
