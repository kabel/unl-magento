<?php

class Unl_Core_Block_Adminhtml_Dashboard_Totals extends Mage_Adminhtml_Block_Dashboard_Totals
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Dashboard_Totals::_prepareLayout()
     * by init'ing the colleciton in order and hiding shipping for filter
     */
    protected function _prepareLayout()
    {
        if (!Mage::helper('core')->isModuleEnabled('Mage_Reports')) {
            return $this;
        }
        $isFilter = $this->getRequest()->getParam('store') || $this->getRequest()->getParam('website') || $this->getRequest()->getParam('group');
        $period = $this->getRequest()->getParam('period', '24h');

        /* @var $collection Mage_Reports_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('reports/order_collection')
            ->checkIsLive($period)
            ->calculateTotals($isFilter)
            ->addCreateAtPeriodFilter($period);

        $storeIds = null;
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getRequest()->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        }

        $storeIds = Mage::helper('unl_core')->getScopeFilteredStores($storeIds);

        if (empty($storeIds) && !$collection->isLive()) {
            $collection->addFieldToFilter('store_id',
                array('eq' => Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE)->getId())
            );
        } else if (!empty($storeIds)) {
            $collection->addFieldToFilter('store_id', array('in' => $storeIds));
        }

        $collection->load();

        $totals = $collection->getFirstItem();

        $this->addTotal($this->__('Revenue'), $totals->getRevenue());
        $this->addTotal($this->__('Tax'), $totals->getTax());
        if (empty($storeIds)) {
            $this->addTotal($this->__('Shipping'), $totals->getShipping());
        }
        $this->addTotal($this->__('Quantity'), $totals->getQuantity()*1, true);
    }
}
