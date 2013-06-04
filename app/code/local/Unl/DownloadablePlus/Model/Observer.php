<?php

class Unl_DownloadablePlus_Model_Observer
{
    /**
     * A <i>frontend</i> and <i>adminhtml</i> event observer for the
     * <code>sales_order_save_commit_after</code> event.
     *
     * It extends the Mage_Downloadable module's observer by adding special
     * link status logic for Invoicelater payment method orders.
     *
     * @see Mage_Downloadable_Model_Observer::setLinkStatus()
     * @param Varien_Event_Observer $observer
     */
    public function setLinkStatus($observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order->getId()) {
            //order not saved in the database
            return $this;
        }

        /* @var $order Mage_Sales_Model_Order */
        $linkStatuses = array(
            'expired'         => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED,
            'avail'           => Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE,
        );
        $downloadableItemsStatuses = array();

        if ($order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $paymentMethod = $order->getPayment()->getMethodInstance();
            if ($paymentMethod instanceof Unl_Core_Model_Payment_Method_Invoicelater) {
                $status = $linkStatuses['avail'];

                foreach ($order->getAllItems() as $item) {
                    if ($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                        || $item->getRealProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
                    ) {
                        $downloadableItemsStatuses[$item->getId()] = $status;
                    }
                }

                if ($downloadableItemsStatuses) {
                    $linkPurchased = Mage::getResourceModel('downloadable/link_purchased_item_collection')
                        ->addFieldToFilter('order_item_id', array('in' => array_keys($downloadableItemsStatuses)));

                    foreach ($linkPurchased as $link) {
                        if ($link->getStatus() != $linkStatuses['expired']
                            && !empty($downloadableItemsStatuses[$link->getOrderItemId()])
                        ) {
                            $link->setStatus($downloadableItemsStatuses[$link->getOrderItemId()])
                                ->save();
                        }
                    }
                }

                return $this;
            }
        }

        return Mage::getSingleton('downloadable/observer')->setLinkStatus($observer);
    }
}
