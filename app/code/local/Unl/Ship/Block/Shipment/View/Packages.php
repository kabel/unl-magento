<?php

class Unl_Ship_Block_Shipment_View_Packages extends Mage_Adminhtml_Block_Template
{
	/**
     * Retrieve shipment model instance
     *
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    /**
     * Retrieve a collection of packages for the current shipment
     *
     * @return Unl_Ship_Model_Resource_Shipment_Package_Collection
     */
    public function getPackages()
    {
        /* @var $collection Unl_Ship_Model_Resource_Shipment_Package_Collection */
        $collection = Mage::getModel('unl_ship/shipment_package')->getResourceCollection();
        $collection->addFieldToFilter('shipment_id', $this->getShipment()->getId());

        return $collection;
    }

    public function getCarrierTitle($code)
    {
        if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
            return $carrier->getConfigData('title');
        }

        return Mage::helper('sales')->__('Custom Value');
    }
}
