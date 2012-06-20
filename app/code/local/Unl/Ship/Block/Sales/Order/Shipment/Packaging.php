<?php

class Unl_Ship_Block_Sales_Order_Shipment_Packaging extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging
{
    /**
     * Retrieve a collection of packages for the current shipment
     *
     * @return Unl_Ship_Model_Resource_Shipment_Package_Collection
     */
    public function getAdditionalPackages()
    {
        if (is_null($this->getShipment()->getAddlPackages())) {
            Mage::helper('unl_ship')->loadUnlPackages($this->getShipment());
        }

        return $this->getShipment()->getAddlPackages();
    }
}
