<?php

class Unl_Core_Block_Adminhtml_Dashboard_Searches_Top extends Mage_Adminhtml_Block_Dashboard_Searches_Top
{
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_CatalogSearch')) {
            return parent::_prepareCollection();
        }
        $this->_collection = Mage::getModel('catalogsearch/query')
            ->getResourceCollection();

        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else {
            $storeIds = null;
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        $this->_collection
            ->setPopularQueryFilter($storeIds);

        $this->setCollection($this->_collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }

}
