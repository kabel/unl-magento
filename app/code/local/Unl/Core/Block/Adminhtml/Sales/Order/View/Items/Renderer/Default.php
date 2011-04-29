<?php

class Unl_Core_Block_Adminhtml_Sales_Order_View_Items_Renderer_Default
    extends Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Sales_Order_View_Items_Renderer_Default::canDisplayGiftmessage()
     * by only allowing existing messages to be shown
     */
    public function canDisplayGiftmessage()
    {
        return $this->getItem()->getGiftMessageId();
    }
}
