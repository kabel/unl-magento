<?php

class Unl_DownloadablePlus_Block_Customer_Products_List extends Mage_Downloadable_Block_Customer_Products_List
{
    public function __construct()
    {
        Mage_Core_Block_Template::__construct();
    }

    protected function _prepareLayout()
    {
        Mage_Core_Block_Template::_prepareLayout();

        $session = Mage::getSingleton('customer/session');

        /* @var $orders Mage_Sales_Model_Resource_Order_Collection */
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToSelect('entity_id');

        $cond = $orders->getConnection()->quoteInto('main_table.entity_id = p.order_id AND p.customer_id = ?', $session->getCustomerId());
        $orders->join(array('p' => 'downloadable/link_purchased'), $cond, array())
            ->addOrder('main_table.created_at');

        $orders->getSelect()->group('entity_id');

        $this->setItems($orders);

        $pager = $this->getLayout()->createBlock('page/html_pager', 'downloadable.customer.products.pager')
            ->setCollection($orders);

        $pager->setAvailableLimit(array(10));
        $pager->setTotalLabel('order(s) with digital content');

        Mage::helper('unl_core')->prepareDualPager($pager);

        $this->setChild('pager', $pager);
        return $this;
    }

    /**
     * Returns a collection of purchased links of a given order id
     *
     * @param int $orderId
     * @return Mage_Downloadable_Model_Resource_Link_Purchased_Collection
     */
    public function getPurchasesByOrderId($orderId)
    {
        $collection = Mage::getResourceModel('downloadable/link_purchased_collection')
            ->addFieldToFilter('order_id', $orderId)
            ->addPurchasedItemsToResult()
            ->addFieldToFilter('status',
                array(
                    'nin' => array(
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
                    )
                )
            );

        return $collection;
    }
}
