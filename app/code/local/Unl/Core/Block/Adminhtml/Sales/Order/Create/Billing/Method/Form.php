<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Create_Billing_Method_Form
    extends Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form
{
    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Order_Create_Billing_Method_Form::_canUseMethod()
     * by also checking ACL for gateway permissions
     */
    protected function _canUseMethod($method)
    {
        if ($method->isGateway() &&
            !Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/submit_gateway')
        ) {
            return false;
        }
        return parent::_canUseMethod($method);
    }
}
