<?php

class Unl_Core_Block_Adminhtml_Dashboard_Tab_Customers_Most extends Mage_Adminhtml_Block_Dashboard_Tab_Customers_Most
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('reports/order_collection');
        /* @var $collection Mage_Reports_Model_Mysql4_Order_Collection */
        $collection
            ->groupByCustomer()
            ->addOrdersCount()
            ->joinCustomerName();

        $storeFilter = 0;
        if ($this->getParam('store')) {
            $collection->filterScope(array($this->getParam('store')));
            $storeFilter = 1;
        } else if ($this->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));
        } else if ($this->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            $collection->filterScope($storeIds);
        }

        $collection->addSumAvgTotals($storeFilter)
            ->orderByTotalAmount();

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}