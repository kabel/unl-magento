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

    public function addOrderBlockParentButtons()
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_View */
        $block = $this->getParentBlock();
        $order = $block->getOrder();

        if ($this->_isAllowedAction('cancel') && $order->canBackOut()) {
            $message = Mage::helper('sales')->__('Are you sure you want to back out this order?');
            $block->addButton('order_backout', array(
                'label'     => Mage::helper('sales')->__('Back Out'),
                'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . $block->getUrl('*/*/backOut') . '\')',
            ));
        }
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
