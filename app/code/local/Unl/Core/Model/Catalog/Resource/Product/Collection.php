<?php

class Unl_Core_Model_Catalog_Resource_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected function _renderFiltersBefore()
    {
        Mage::dispatchEvent('catalog_product_collection_render_filters_before', array('collection' => $this));
        return parent::_renderFiltersBefore();
    }
}
