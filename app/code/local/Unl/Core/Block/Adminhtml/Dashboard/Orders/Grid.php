<?php

class Unl_Core_Block_Adminhtml_Dashboard_Orders_Grid extends Mage_Adminhtml_Block_Dashboard_Orders_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Orders_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt();

        if($this->getParam('store') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $storeIds = array($this->getParam('store'));
            } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            }
            $collection->filterScope($storeIds);

            $collection->addRevenueToSelect();
        } else if ($this->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', array('in' => $storeIds));

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }
}
