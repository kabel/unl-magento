<?php

class Unl_Core_Block_Adminhtml_Dashboard_Tab_Products_Ordered extends Mage_Adminhtml_Block_Dashboard_Tab_Products_Ordered
{
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Sales')) {
            return $this;
        }
        $storeIds = null;
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
        } else if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        $collection = Mage::getResourceModel('sales/report_bestsellers_collection')
            ->setModel('catalog/product')
            ->addStoreFilter($storeIds)
        ;

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}
