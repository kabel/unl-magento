<?php

class Unl_Core_Block_Adminhtml_Dashboard_Searches_Last extends Mage_Adminhtml_Block_Dashboard_Searches_Last
{
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_CatalogSearch')) {
            return parent::_prepareCollection();
        }
        $this->_collection = Mage::getModel('catalogsearch/query')
            ->getResourceCollection();
        $this->_collection->setRecentQueryFilter();

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
            $this->_collection->addFieldToFilter('store_id', array('in' => $storeIds));
        }

        $this->setCollection($this->_collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}
