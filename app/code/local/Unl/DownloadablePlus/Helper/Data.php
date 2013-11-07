<?php

class Unl_DownloadablePlus_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the URL to reset the use of a download link, or
     * false if this cannot be done
     *
     * @param Mage_Downloadable_Model_Link_Purchased_Item $link
     * @param Mage_Sales_Model_Order $order
     */
    public function getResetDownloadUrl($link, $order)
    {
        if (!$this->canResetDownload($link, $order)) {
            return false;
        }

        return $this->_getUrl('*/downloadable_link_purchased/resetUse', array('order_id' => $order->getId(), 'item_id' => $link->getId()));
    }

    /**
     * Returns if the given link can be reset from given order
     *
     * @param Mage_Downloadable_Model_Link_Purchased_Item $link
     * @param Mage_Sales_Model_Order $order
     */
    public function canResetDownload($link, $order)
    {
        if (in_array($order->getState(), array(
            Mage_Sales_Model_Order::STATE_CLOSED,
            Mage_Sales_Model_Order::STATE_CANCELED
        ))) {
            return false;
        }

        if ($link->getNumberOfDownloadsBought() && $link->getStatus() == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
            return true;
        }

        return false;
    }
}
