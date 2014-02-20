<?php

class Unl_Notify_Model_Observer
{
    const XML_PATH_ORDER_NOTIFY_ENABLED  = 'sales_email/order_notify/enabled';
    const XML_PATH_ORDER_NOTIFY_COUNT    = 'sales_email/order_notify/count';
    const XML_PATH_ORDER_NOTIFY_TEMPLATE = 'sales_email/order_notify/template';
    const XML_PATH_ORDER_NOTIFY_IDENTITY = 'sales_email/order_notify/identity';

    /**
     * Checks the order for items that have notification addresses
     * and adds them to the queue table
     *
     * @param Varien_Event_Observer $observer
     */
    public function queueOrderNotify($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();

        if ($this->canSendOrderNotifications()) {
            $queue = Mage::getModel('unl_notify/queue');
            $queue->setOrderId($order->getId());
            $queue->save();
        }

        return $this;
    }

    public function sendNotifications()
    {
        /* @var $queueCollection Unl_Notify_Model_Resource_Queue_Collection */
        $queueCollection = Mage::getModel('unl_notify/queue')->getResourceCollection();
        $queueCollection->setPageSize(Mage::getStoreConfig(self::XML_PATH_ORDER_NOTIFY_COUNT));

        $orderIds = array();
        foreach ($queueCollection as $queue) {
            $orderIds[] = $queue->getOrderId();
            $queue->delete();
        }

        if (empty($orderIds)) {
            return $this;
        }

        /* @var $orderItems Mage_Sales_Model_Resource_Order_Item_Collection */
        $orderItems = Mage::getModel('sales/order_item')->getResourceCollection();
        $orderItems->addFieldToFilter('order_id', array('in' => $orderIds));

        $productIds = array();
        foreach ($orderItems as $item) {
            if (!in_array($item->getProductId(), $productIds)) {
                $productIds[] = $item->getProductId();
            }
        }

        /* @var $products Mage_Catalog_Model_Resource_Product_Collection */
        $products = Mage::getModel('catalog/product')->getResourceCollection();
        $products->addAttributeToFilter('notify_emails', array('neq' => ''))
            ->addAttributeToFilter('entity_id', array('in' => $productIds));

        foreach ($orderItems as $item) {
            if ($product = $products->getItemById($item->getProductId())) {
                // send the transactional email for each product
                $order = $item->getOrder();
                $customerName = $order->getCustomerIsGuest() ? $order->getBillingAddress()->getName() : $order->getCustomerName();
                $storeId = $order->getStore()->getId();
                $templateId = Mage::getStoreConfig(self::XML_PATH_ORDER_NOTIFY_TEMPLATE, $storeId);
                $sendTo = explode(',', preg_replace('/\s+/', '', $product->getNotifyEmails()));

                // Start store emulation process
                $appEmulation = Mage::getSingleton('core/app_emulation');
                $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

                try {
                    // Retrieve specified view block from appropriate design package (depends on emulated store)
                    $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
                        ->setIsSecureMode(true);
                    $paymentBlock->getMethod()->setStore($storeId);
                    $paymentBlockHtml = $paymentBlock->toHtml();
                } catch (Exception $exception) {
                    // Stop store emulation process
                    $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
                    throw $exception;
                }

                // Stop store emulation process
                $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

                $mailer = Mage::getModel('core/email_template_mailer');
                $emailInfo = Mage::getModel('core/email_info');
                foreach ($sendTo as $to) {
                    $emailInfo->addTo($to);
                }
                $mailer->addEmailInfo($emailInfo);

                // Set all required params and send emails
                $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_ORDER_NOTIFY_IDENTITY, $storeId));
                $mailer->setStoreId($storeId);
                $mailer->setTemplateId($templateId);
                $mailer->setTemplateParams(array(
                    'order' => $order,
                    'item'  => $item,
                    'customer_name' => $customerName,
                    'payment_html' => $paymentBlockHtml,
                ));
                $mailer->send();
            }
        }

        return $this;
    }

    public function canSendOrderNotifications($store = null)
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ORDER_NOTIFY_ENABLED, $store);
    }
}
