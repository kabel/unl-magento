<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Shipment_View_Packages extends Mage_Adminhtml_Block_Template
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
     * @return Zenprint_Ordership_Model_Mysql4_Shipment_Package_Collection
     */
    public function getPackages()
    {
        /* @var $collection Zenprint_Ordership_Model_Mysql4_Shipment_Package_Collection */
        $collection = Mage::getModel('shipping/shipment_package')->getResourceCollection();
        $collection->getSelect()->where('order_shipment_id = ?', $this->getShipment()->getEntityId());
        
        return $collection;
    }
    
    public function getCarrierTitle($code)
    {
        if ($carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($code)) {
            return $carrier->getConfigData('title');
        }
        else {
            return Mage::helper('sales')->__('Custom Value');
        }
        return false;
    }
}