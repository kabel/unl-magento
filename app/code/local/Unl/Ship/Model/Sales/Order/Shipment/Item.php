<?php

class Unl_Ship_Model_Sales_Order_Shipment_Item extends Mage_Sales_Model_Order_Shipment_Item
{
    public function unregister()
    {
        $this->getOrderItem()->setQtyShipped(
            $this->getOrderItem()->getQtyShipped()-$this->getQty()
        );
        $this->isDeleted(true);
        return $this;
    }
}
