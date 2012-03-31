<?php

class Unl_Core_Block_Adminhtml_Dashboard_Tab_Products_Viewed extends Mage_Adminhtml_Block_Dashboard_Tab_Products_Viewed
{
    /* Extends
     * @see Mage_Adminhtml_Block_Widget_Grid::setCollection()
     * by adding scope filters
     */
    public function setCollection($collection)
    {
        $storeIds = array();
        if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
        } else if ($this->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
        } else if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        }

        Mage::helper('unl_core')->addProductAdminScopeFilters($collection, $storeIds);

        return parent::setCollection($collection);
    }
}
