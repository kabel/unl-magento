<?php

class Unl_Core_Model_ImportExport_Export_Entity_Product extends Mage_ImportExport_Model_Export_Entity_Product
{
    protected function _prepareEntityCollection(Mage_Eav_Model_Entity_Collection_Abstract $collection)
    {
        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $collection->addAttributeToFilter('source_store_view', array('in' => $scope));
        }
        return parent::_prepareEntityCollection($collection);
    }
}
