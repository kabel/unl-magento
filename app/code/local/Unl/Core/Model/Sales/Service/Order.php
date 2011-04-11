<?php

class Unl_Core_Model_Sales_Service_Order extends Mage_Sales_Model_Service_Order
{
    /**
     * Prepare order creditmemo based on order items and requested params
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Creditmemo
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
                $qty = $orderItem->getQtyOrdered();
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

    /**
     * Prepare order creditmemo based on invoice items and requested requested params
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Creditmemo
     */
    public function prepareInvoiceCreditmemo($invoice, $data = array())
    {
        $totalQty = 0;
        $qtys = isset($data['qtys']) ? $data['qtys'] : array();
        $creditmemo = $this->_convertor->toCreditmemo($this->_order);
        $creditmemo->setInvoice($invoice);
        foreach ($invoice->getAllItems() as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();
            if (!$this->_canRefundItem($orderItem, $qtys)) {
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
            $baseAllowedAmount = $order->getBaseShippingAmount()-$order->getBaseShippingRefunded();
            $creditmemo->setBaseShippingAmount(min($baseAllowedAmount, $invoice->getBaseShippingAmount()));
        }

        Mage::dispatchEvent('sales_model_service_order_prepare_creditmemo', array('order'=>$this->_order, 'creditmemo'=>$creditmemo));

        $creditmemo->collectTotals();
        return $creditmemo;
    }

    /**
     * Prepare order invoice based on order data and requested items qtys
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Invoice
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
                $qty = $orderItem->getQtyOrdered();
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

    /**
     * Prepare order shipment based on order items and requested items qty
     *
     * @param array $data
     * @return Mage_Sales_Model_Order_Shipment
     */
    public function prepareShipment($qtys = array())
    {
        $totalQty = 0;
        $shipment = $this->_convertor->toShipment($this->_order);
        foreach ($this->_order->getAllItems() as $orderItem) {
            if (!$this->_canShipItem($orderItem, $qtys)) {
                continue;
            }

            $item = $this->_convertor->itemToShipmentItem($orderItem);
            if ($orderItem->isDummy(true)) {
                $qty = 1;
            } else {
                if (isset($qtys[$orderItem->getId()])) {
                    $qty = min($qtys[$orderItem->getId()], $orderItem->getQtyToShip());
                } elseif (!count($qtys)) {
                    $qty = $orderItem->getQtyToShip();
                } else {
                    continue;
                }
            }

            $totalQty += $qty;
            $item->setQty($qty);
            $shipment->addItem($item);
        }
        $shipment->setTotalQty($totalQty);
        return $shipment;
    }
}