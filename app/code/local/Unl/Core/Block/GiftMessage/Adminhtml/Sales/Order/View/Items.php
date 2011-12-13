<?php

class Unl_Core_Block_GiftMessage_Adminhtml_Sales_Order_View_Items
    extends Mage_GiftMessage_Block_Adminhtml_Sales_Order_View_Items
{
    /* Overrides
     * @see Mage_GiftMessage_Block_Adminhtml_Sales_Order_View_Items::canDisplayGiftmessage()
     * by using display logic from helper
     */
    public function canDisplayGiftmessage()
    {
        return $this->helper('giftmessage/message')->isMessagesAvailable('order_item',
            $this->getItem(), $this->getItem()->getOrder()->getStoreId()
        );
    }
}
