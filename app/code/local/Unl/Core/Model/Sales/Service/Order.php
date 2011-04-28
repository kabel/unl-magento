<?php

class Unl_Core_Model_Sales_Service_Order extends Mage_Sales_Model_Service_Order
{

    /* Overrides logic of
     * @see Mage_Sales_Model_Service_Order::prepareCreditmemo()
     * by changing the dummy Qty and adding an event before total collection
     */
    public function prepareCreditmemo($data = array())
    {
        $totalQty = 0;
        $creditmemo = $this->_convertor->toCreditmemo($this->_order);
        $qtys = isset($data['qtys']) ? $data['qtys'] : array();

        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canRefundItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->_convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float) $qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);

        Mage::dispatchEvent('sales_model_service_order_prepare_creditmemo', array('order'=>$this->_order, 'creditmemo'=>$creditmemo));

        $creditmemo->collectTotals();
        return $creditmemo;
    }


    /* Overrides the logic of
     * @see Mage_Sales_Model_Service_Order::prepareInvoiceCreditmemo()
     * by changing the dummy Qty and adding an event before total collection
     */
    public function prepareInvoiceCreditmemo($invoice, $data = array())
    {
        $totalQty = 0;
        $qtys = isset($data['qtys']) ? $data['qtys'] : array();
        $creditmemo = $this->_convertor->toCreditmemo($this->_order);
        $creditmemo->setInvoice($invoice);

        $invoiceQtysRefunded = array();
        foreach($invoice->getOrder()->getCreditmemosCollection() as $createdCreditmemo) {
            if ($createdCreditmemo->getState() != Mage_Sales_Model_Order_Creditmemo::STATE_CANCELED
                && $createdCreditmemo->getInvoiceId() == $invoice->getId()) {
                foreach($createdCreditmemo->getAllItems() as $createdCreditmemoItem) {
                    $orderItemId = $createdCreditmemoItem->getOrderItem()->getId();
                    if (isset($invoiceQtysRefunded[$orderItemId])) {
                        $invoiceQtysRefunded[$orderItemId] += $createdCreditmemoItem->getQty();
                    } else {
                        $invoiceQtysRefunded[$orderItemId] = $createdCreditmemoItem->getQty();
                    }
                }
            }
        }

        $invoiceQtysRefundLimits = array();
        foreach($invoice->getAllItems() as $invoiceItem) {
            $invoiceQtyCanBeRefunded = $invoiceItem->getQty();
            $orderItemId = $invoiceItem->getOrderItem()->getId();
            if (isset($invoiceQtysRefunded[$orderItemId])) {
                $invoiceQtyCanBeRefunded = $invoiceQtyCanBeRefunded - $invoiceQtysRefunded[$orderItemId];
            }
            $invoiceQtysRefundLimits[$orderItemId] = $invoiceQtyCanBeRefunded;
        }


        foreach ($invoice->getAllItems() as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();

            if (!$this->_canRefundItem($orderItem, $qtys, $invoiceQtysRefundLimits)) {
                continue;
            }

            $item = $this->_convertor->itemToCreditmemoItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered();
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float) $qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToRefund();
                } else {
                    continue;
                }
                if (isset($invoiceQtysRefundLimits[$orderItem->getId()])) {
                    $qty = min($qty, $invoiceQtysRefundLimits[$orderItem->getId()]);
                }
            }
            $qty = min($qty, $invoiceItem->getQty());
            $totalQty += $qty;
            $item->setQty($qty);
            $creditmemo->addItem($item);
        }
        $creditmemo->setTotalQty($totalQty);

        $this->_initCreditmemoData($creditmemo, $data);
        if (!isset($data['shipping_amount'])) {
            $order = $invoice->getOrder();
            $isShippingInclTax = Mage::getSingleton('tax/config')->displaySalesShippingInclTax($order->getStoreId());
            if ($isShippingInclTax) {
                $baseAllowedAmount = $order->getBaseShippingInclTax()
                        - $order->getBaseShippingRefunded()
                        - $order->getBaseShippingTaxRefunded();
            } else {
                $baseAllowedAmount = $order->getBaseShippingAmount() - $order->getBaseShippingRefunded();
                $baseAllowedAmount = min($baseAllowedAmount, $invoice->getBaseShippingAmount());
            }
            $creditmemo->setBaseShippingAmount($baseAllowedAmount);
        }

        Mage::dispatchEvent('sales_model_service_order_prepare_creditmemo', array('order'=>$this->_order, 'creditmemo'=>$creditmemo));

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /* Overrides the logic of
     * @see Mage_Sales_Model_Service_Order::prepareInvoice()
     * by adding an event before total collection
     */
    public function prepareInvoice($qtys = array())
    {
        $invoice = $this->_convertor->toInvoice($this->_order);
        $totalQty = 0;
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canInvoiceItem($orderItem, $qtys)) {
                continue;
            }
            $item = $this->_convertor->itemToInvoiceItem($orderItem);
            if ($orderItem->isDummy()) {
                $qty = $orderItem->getQtyOrdered() ? $orderItem->getQtyOrdered() : 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = (float) $qtys[$orderItem->getId()];
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToInvoice();
                } else {
                    continue;
                }
            }
            $totalQty += $qty;
            $item->setQty($qty);
            $invoice->addItem($item);
        }
        $invoice->setTotalQty($totalQty);

        Mage::dispatchEvent('sales_model_service_order_prepare_invoice', array('order'=>$this->_order, 'invoice'=>$invoice));

        $invoice->collectTotals();
        $this->_order->getInvoiceCollection()->addItem($invoice);
        return $invoice;
    }
}