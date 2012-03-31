<?php

class Unl_Core_Model_ImportExport_Export_Entity_Product extends Mage_ImportExport_Model_Export_Entity_Product
{
    /* Extends
     * @see Mage_ImportExport_Model_Export_Entity_Abstract::_prepareEntityCollection()
     * by adding admin scope filters
     */
    protected function _prepareEntityCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        Mage::helper('unl_core')->addProductAdminScopeFilters($collection);

        return parent::_prepareEntityCollection($collection);
    }
}
