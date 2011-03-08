<?php


class Unl_Core_Model_Sales_Observer
{
    /**
     * Sets the quote_item's source store view
     *
     * @param $observer Varien_Event_Observer
     */
    public function setQuoteItemSourceStore($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $quote_item = $observer->getEvent()->getQuoteItem();

        $sourceStore = $product->getSourceStoreView();
        $warehouse   = $product->getWarehouse();

        if (null !== $sourceStore) {
            $quote_item->setSourceStoreView($sourceStore);
        }

        if (null !== $warehouse) {
            $quote_item->setWarehouse($warehouse);
        }
    }

    public function onShippingMethodSet($observer)
    {
        /* @var $address Unl_Core_Model_Sales_Quote_Address */
        $address = $observer->getEvent()->getQuoteAddress();
        $method = $address->getShippingMethod();

        if (!empty($method) && strpos($method, 'pickup_store') === 0) {
            $address->getShippingRateByCode($method)->getCarrierInstance()->updateAddress($address);
            $address->save();
        }
    }

    /**
     * Adds some missing data members of quote_address_item to order_item from quote_item
     *
     * @param $observer Varien_Event_Observer
     */
    public function onSalesConvertQuoteItemToOrderItem($observer)
    {
        $item = $observer->getEvent()->getItem();

        if ($item instanceof Mage_Sales_Model_Quote_Address_Item) {
            $quoteItem = $item->getQuoteItem();
            $orderItem = $observer->getEvent()->getOrderItem();

            $orderItem->setStoreId($quoteItem->getStoreId())
                ->setSourceStoreView($quoteItem->getSourceStoreView())
                ->setWarehouse($quoteItem->getWarehouse());

            //TODO: Fix tax_percent issue displayed in adminhtml
//            if (null === $orderItem->getTaxPercent()) {
//
//            }
        }
    }

    public function initShipmentGridVirtualColumns($observer)
    {
        /* @var $resource Mage_Sales_Model_Mysql4_Order_Shipment */
        $resource = $observer->getEvent()->getResource();
        $resource->addVirtualGridColumn(
                'shipping_description',
                'sales/order',
                array('order_id' => 'entity_id'),
                'shipping_description'
            )
            ->addVirtualGridColumn(
                'base_shipping_amount',
                'sales/order',
                array('order_id' => 'entity_id'),
                'base_shipping_amount'
            )
            ->addVirtualGridColumn(
                'shipping_amount',
                'sales/order',
                array('order_id' => 'entity_id'),
                'shipping_amount'
            );
    }

    public function onSalesOrderCreditmemoRefund($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $creditmemo->setRefundedAt(now());
    }

    public function onSalesOrderInvoicePay($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $invoice->setPaidAt(now());
    }

    public function onBeforeSalesItemSave($observer)
    {
        $item = $observer->getEvent()->getDataObject();

        switch (true) {
            case $item instanceof Mage_Sales_Model_Order_Item:
                if (!$item->hasIsDummy()) {
                    $item->setIsDummy($item->isDummy());
                }
                break;
            case $item instanceof Mage_Sales_Model_Order_Invoice_Item:
            case $item instanceof Mage_Sales_Model_Order_Creditmemo_Item:
                if (!$item->hasIsDummy()) {
                    $item->setIsDummy($item->getOrderItem()->isDummy());
                }
                break;
            case $item instanceof Mage_Sales_Model_Order_Shipment_Item:
                if (!$item->hasIsDummy()) {
                    $item->setIsDummy($item->getOrderItem()->isDummy(true));
                }
                break;
        }
    }
}