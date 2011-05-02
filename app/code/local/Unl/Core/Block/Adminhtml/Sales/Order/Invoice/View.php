<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Invoice_View extends Mage_Adminhtml_Block_Sales_Order_Invoice_View
{
    public function __construct()
    {
        $this->_objectId    = 'invoice_id';
        $this->_controller  = 'sales_order_invoice';
        $this->_mode        = 'view';
        $this->_session = Mage::getSingleton('admin/session');

        Mage_Adminhtml_Block_Widget_Form_Container::__construct();

        $this->_removeButton('save');
        $this->_removeButton('reset');
        $this->_removeButton('delete');

        if ($this->_isAllowedAction('cancel') && $this->getInvoice()->canCancel()) {
            $this->_addButton('cancel', array(
                'label'     => Mage::helper('sales')->__('Cancel'),
                'class'     => 'delete',
                'onclick'   => 'setLocation(\''.$this->getCancelUrl().'\')'
                )
            );
        }

        if ($this->_isAllowedAction('emails')) {
            $this->addButton('send_notification', array(
                'label'     => Mage::helper('sales')->__('Send Email'),
                'onclick'   => 'confirmSetLocation(\''
                . Mage::helper('sales')->__('Are you sure you want to send Invoice email to customer?')
                . '\', \'' . $this->getEmailUrl() . '\')'
            ));
        }

        $orderPayment = $this->getInvoice()->getOrder()->getPayment();

        if ($this->_isAllowedAction('creditmemo') && $this->getInvoice()->getOrder()->canCreditmemo()) {
            if (($orderPayment->canRefundPartialPerInvoice()
                && $this->getInvoice()->canRefund()
                && $orderPayment->getAmountPaid() > $orderPayment->getAmountRefunded())
                || ($orderPayment->canRefund() && !$this->getInvoice()->getIsUsedForRefund())) {
                $this->_addButton('capture', array( // capture?
                    'label'     => Mage::helper('sales')->__('Credit Memo'),
                    'class'     => 'go',
                    'onclick'   => 'setLocation(\''.$this->getCreditMemoUrl().'\')'
                    )
                );
            }
        }

        if ($this->_isAllowedAction('capture') && $this->getInvoice()->canCapture()) {
            $this->_addButton('capture', array(
                'label'     => Mage::helper('sales')->__('Capture'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getCaptureUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canForcePay()) {
            $this->_addButton('forcepay', array(
                'label'     => Mage::helper('sales')->__('Mark Paid'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getForcePayUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canWriteOff()) {
            $this->_addButton('writeoff', array(
                'label'     => Mage::helper('sales')->__('Write-Off'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getWriteOffUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->canVoid()) {
            $this->_addButton('void', array(
                'label'     => Mage::helper('sales')->__('Void'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getVoidUrl().'\')'
                )
            );
        }

        if ($this->getInvoice()->getId()) {
            $this->_addButton('print', array(
                'label'     => Mage::helper('sales')->__('Print'),
                'class'     => 'save',
                'onclick'   => 'setLocation(\''.$this->getPrintUrl().'\')'
                )
            );
        }
    }

    public function getForcePayUrl()
    {
        return $this->getUrl('unl_core/sales_order_invoice/forcepay', array('invoice_id' => $this->getInvoice()->getId()));
    }

    public function getWriteOffUrl()
    {
        return $this->getUrl('unl_core/sales_order_invoice/writeoff', array('invoice_id' => $this->getInvoice()->getId()));
    }
}
