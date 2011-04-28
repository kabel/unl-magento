<?php

class Unl_Core_Block_Adminhtml_Report_Product_Downloads_Grid extends Mage_Adminhtml_Block_Report_Product_Downloads_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Report_Product_Downloads_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        if ($this->getRequest()->getParam('website')) {
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('group')) {
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
            $storeId = array_pop($storeIds);
        } else if ($this->getRequest()->getParam('store')) {
            $storeId = (int)$this->getRequest()->getParam('store');
        } else {
            $storeId = '';
        }

        $collection = Mage::getResourceModel('reports/product_downloads_collection')
            ->addAttributeToSelect('*')
            ->setStoreId($storeId)
            ->addAttributeToFilter('type_id', array(Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE))
            ->addSummary();

        if( $storeId ) {
            $collection->addStoreFilter($storeId);
            $collection->addAttributeToFilter('source_store_view', array('eq' => $storeId));
        }

        $this->setCollection($collection);
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}
