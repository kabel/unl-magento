<?php

require_once 'Mage/Adminhtml/controllers/Sales/Order/InvoiceController.php';

class Unl_Core_Adminhtml_Sales_Order_InvoiceController extends Mage_Adminhtml_Sales_Order_InvoiceController
{
    /**
     * Mark an invoice as paid, if allowed to force it
     *
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

    /**
     * Sets the status of an invoice to a special terminal status
     * of "Write Off"
     *
     */
    public function writeoffAction()
    {
        if ($invoice = $this->_initInvoice()) {
            try {
                if ($invoice->canWriteOff()) {
                    $invoice->writeOff();
                    $this->_saveInvoice($invoice);
                    $this->_getSession()->addSuccess($this->__('The invoice has been written off.'));
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
}
