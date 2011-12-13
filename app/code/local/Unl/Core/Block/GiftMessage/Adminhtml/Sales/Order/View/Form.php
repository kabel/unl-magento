<?php

class Unl_Core_Block_GiftMessage_Adminhtml_Sales_Order_View_Form
    extends Mage_GiftMessage_Block_Adminhtml_Sales_Order_View_Form
{
    /* Overrides
     * @see Mage_GiftMessage_Block_Adminhtml_Sales_Order_View_Form::canDisplayGiftmessageForm()
     * by using the display logic from helper
     */
    public function canDisplayGiftmessageForm()
    {
        $order = Mage::registry('current_order');
        if ($order) {
            foreach ($order->getAllItems() as $item) {
                if ($this->helper('giftmessage/message')->isMessagesAvailable('order_item',
                        $item, $order->getStoreId()
                    )) {
                    return true;
                }
            }
        }
        return false;
    }
}
