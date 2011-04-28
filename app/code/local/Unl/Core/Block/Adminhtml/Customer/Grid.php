<?php

class Unl_Core_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Customer_Grid::_prepareCollection()
     * to add advanced filter
     */
    protected function _prepareCollection()
    {
        // FROM PARENT
        /* @var $collection Mage_Customer_Model_Entity_Customer_Collection */
        $collection = Mage::getResourceModel('customer/customer_collection')
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('created_at')
            ->addAttributeToSelect('group_id')
            ->joinAttribute('billing_postcode', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('billing_city', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('billing_telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('billing_region', 'customer_address/region', 'default_billing', null, 'left')
            ->joinAttribute('billing_country_id', 'customer_address/country_id', 'default_billing', null, 'left');

        $this->setCollection($collection);
        // END FROM PARENT

        $advfilter = Mage::helper('unl_core')->getAdvancedGridFilters('customer');
        if (!empty($advfilter) && $advfilter->hasData()) {
            if ($advfilter->getData('is_subscriber')) {
                $collection->getSelect()->join(array('s' => $collection->getTable('newsletter/subscriber')),
                    's.customer_id = e.entity_id AND s.subscriber_status = ' . Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED, array());
            }

            $addOrders = false;
            /* @var $orders  Mage_Sales_Model_Mysql4_Order_Collection */
            $orders = Mage::getModel('sales/order')->getResourceCollection();
            $orders->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('customer_id'))
                ->group('customer_id');

            if ($advfilter->getData('purchase_from') || $advfilter->getData('purchase_to')) {
                $addOrders = true;
                $orders->addFieldToFilter('main_table.created_at', array(
                    'from' => $advfilter->getData('purchase_from'),
                    'to' => $advfilter->getData('purchase_to'))
                 );
            }

            if ($advfilter->getData('is_repeat')) {
                $addOrders = true;
                $orders->getSelect()->having('COUNT(DISTINCT(main_table.entity_id)) > 1');
            }

            if ($advfilter->getData('from_store') || $advfilter->getData('item_sku')) {
                $addOrders = true;
                $orders->getSelect()->join(array('oi' => $orders->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array());
                if ($advfilter->getData('from_store')) {
                    $orders->getSelect()->where('oi.source_store_view = ?', $advfilter->getData('from_store'));
                }

                if ($advfilter->getData('item_sku')) {
                    $orders->getSelect()->where('oi.sku LIKE ?', $advfilter->getData('item_sku') . '%');
                }
            }

            if ($addOrders) {
                $collection->getSelect()->join(array('o' => $orders->getSelect()), 'o.customer_id = e.entity_id', array());
            }
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}
