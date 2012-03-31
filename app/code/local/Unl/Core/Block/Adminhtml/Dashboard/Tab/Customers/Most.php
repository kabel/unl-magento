<?php

class Unl_Core_Block_Adminhtml_Dashboard_Tab_Customers_Most extends Mage_Adminhtml_Block_Dashboard_Tab_Customers_Most
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Tab_Customers_Most::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('reports/order_collection');
        /* @var $collection Mage_Reports_Model_Resource_Order_Collection */
        $collection
            ->groupByCustomer()
            ->addOrdersCount()
            ->joinCustomerName();

        $storeIds = null;
        $storeFilter = 0;
        if ($this->getParam('store')) {
            $storeIds = array($this->getParam('store'));
            $storeFilter = 1;
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
        }

        $collection->addSumAvgTotals($storeFilter)
            ->orderByTotalAmount();

        Mage::helper('unl_core')->addAdminScopeFilters($collection, 'order_id', false, $storeIds);

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}
