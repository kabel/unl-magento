<?php

class Unl_Ship_Block_Shipment_Create_Form extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    public function getOrder()
    {
        return Mage::registry('current_shipment')->getOrder();
    }

    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/create', array('order_id' => $this->getOrder()->getId()));
    }
}
