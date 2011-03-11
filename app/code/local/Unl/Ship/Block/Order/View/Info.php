<?php

class Unl_Ship_Block_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    public function getCustomerViewUrl()
    {
        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getOrder()->getCustomerId()));
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId));
    }
}