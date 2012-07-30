<?php

class Unl_Ship_Model_Sales_Order_Shipment extends Mage_Sales_Model_Order_Shipment
{
    public function unregister()
    {
        if (!$this->getId()) {
            Mage::throwException(
                Mage::helper('sales')->__('Cannot unregister non-existing shipment')
            );
        }

        foreach ($this->getAllItems() as $item) {
            $item->unregister();
        }

        $this->isDeleted(true);

        return $this;
    }
}
