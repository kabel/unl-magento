<?php

class Unl_Core_Sales_Order_InvoiceController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Initialize invoice model instance
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _initInvoice($update = false)
    {
        $this->_title($this->__('Sales'))->_title($this->__('Invoices'));

        $invoice = false;
        $itemsToInvoice = 0;
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $orderId = $this->getRequest()->getParam('order_id');
        if ($invoiceId) {
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        } elseif ($orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            /**
             * Check order existing
             */
            if (!$order->getId()) {
                $this->_getSession()->addError($this->__('The order no longer exists.'));
                return false;
            }
            /**
             * Check invoice create availability
             */
            if (!$order->canInvoice()) {
                $this->_getSession()->addError($this->__('The order does not allow creating an invoice.'));
                return false;
            }
            $savedQtys = $this->_getItemQtys();
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice($savedQtys);
            if (!$invoice->getTotalQty()) {
                Mage::throwException($this->__('Cannot create an invoice without products.'));
            }
        }

        Mage::register('current_invoice', $invoice);
        return $invoice;
    }
    
    /**
     * Save data for invoice and related order
     *
     * @param   Mage_Sales_Model_Order_Invoice $invoice
     * @return  Mage_Adminhtml_Sales_Order_InvoiceController
     */
    protected function _saveInvoice($invoice)
    {
        $invoice->getOrder()->setIsInProcess(true);
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();

        return $this;
    }
    
    /**
     * Capture invoice action
     */
    public function forcepayAction()
    {
        if ($invoice = $this->_initInvoice()) {
            try {
                if ($invoice->canForcePay()) {
                    $invoice->pay();
                    $invoice->getOrder()->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true);
                    $this->_saveInvoice($invoice);
                    $this->_getSession()->addSuccess($this->__('The invoice has been marked paid.'));
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError($this->__('Invoice save error.'));
            }
            $this->_redirect('adminhtml/sales_order_invoice/view', array('invoice_id'=>$invoice->getId()));
        } else {
            $this->_forward('noRoute');
        }
    }
    
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/invoice');
    }
}