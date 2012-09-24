<?php

class Unl_Core_Model_CatalogSearch_Layer extends Mage_CatalogSearch_Model_Layer
{
    public function apply()
    {
        $collection = $this->getProductCollection();
        $storeId = Mage::app()->getStore()->getId();

        if (Mage::app()->getRequest()->getParam('deep')) {
            $collection->addAttributeToSelect('source_store_view', 'inner');
            //$collection->getSelect()->order($collection->getConnection()->getCheckSql('source_store_view = ' . $storeId, '0', '1'));
        } else {
            $collection->addAttributeToFilter('source_store_view', $storeId);
        }

        return parent::apply();
    }
}
