<?php

class Unl_Core_Block_Adminhtml_Dashboard_Tab_Products_Viewed extends Mage_Adminhtml_Block_Dashboard_Tab_Products_Viewed
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Tab_Products_Viewed::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else {
            $storeId = (int)$this->getParam('store');
        }

        if ($storeId) {
            $collection = Mage::getResourceModel('reports/product_collection')
                ->addAttributeToSelect('*')
                ->addViewsCount()
                ->setStoreId($storeId)
                ->addAttributeToFilter('source_store_view', array('eq' => $storeId));
        } else {
            $collection = Mage::getResourceModel('reports/product_collection')
                ->addAttributeToSelect('*')
                ->addViewsCount()
                ->setStoreId($storeId)
                ->addStoreFilter($storeId);
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}
