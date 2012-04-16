<?php

class Unl_Core_Block_Adminhtml_Dashboard_Orders_Grid extends Mage_Adminhtml_Block_Dashboard_Orders_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Orders_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $collection = Mage::getResourceModel('reports/order_collection')
            ->addItemCountExpr()
            ->joinCustomerName('customer')
            ->orderByCreatedAt();

        $storeIds = null;
        if($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $storeIds = array($this->getParam('store'));
            } else if ($this->getParam('website')){
                $storeIds = Mage::app()->getWebsite($this->getParam('website'))->getStoreIds();
            } else if ($this->getParam('group')){
                $storeIds = Mage::app()->getGroup($this->getParam('group'))->getStoreIds();
            }

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }

        Mage::helper('unl_core')->addAdminScopeFilters($collection, 'entity_id', true, $storeIds);

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Dashboard_Grid::_prepareCollection();
    }

}
