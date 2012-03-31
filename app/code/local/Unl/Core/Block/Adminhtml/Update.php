<?php

class Unl_Core_Block_Adminhtml_Update extends Mage_Adminhtml_Block_Abstract
{
    public function addParentButtons()
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_Invoice_View */
        $block = $this->getParentBlock();

        if ($block->getInvoice()->canForcePay()) {
            $url = $block->getUrl('*/sales_order_invoice/forcepay', array('invoice_id' => $block->getInvoice()->getId()));
            $block->addButton('forcepay', array(
                'label'     => Mage::helper('sales')->__('Mark Paid'),
                'class'     => 'save',
                'onclick'   => "setLocation('{$url}')"
                ), 35);
        }

        if ($block->getInvoice()->canWriteOff()) {
            $url = $block->getUrl('*/sales_order_invoice/writeoff', array('invoice_id' => $block->getInvoice()->getId()));
            $block->addButton('writeoff', array(
                'label'     => Mage::helper('sales')->__('Write-Off'),
                'class'     => 'save',
                'onclick'   => "setLocation('{$url}')"
                ), 0, 36);
        }
    }
}
