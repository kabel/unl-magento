<?php

class Unl_Core_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    /**
     * Extends parent by adding advanced filters.
     *
     * @param $collection Mage_Customer_Model_Entity_Customer_Collection
     */
    public function setCollection($collection)
    {
        $collection->joinAttribute('billing_company', 'customer_address/company', 'default_billing', null, 'left');

        $advfilter = Mage::helper('unl_core')->getAdvancedGridFilters('customer');
        if (!empty($advfilter) && $advfilter->hasData()) {
            if ($advfilter->getData('is_subscriber')) {
                $collection->getSelect()->join(
                    array('s' => $collection->getTable('newsletter/subscriber')),
                    's.customer_id = e.entity_id AND s.subscriber_status = '. Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED,
                    array()
                );
            }

            $helper = Mage::getResourceHelper('core');
            $addOrders = false;
            /* @var $orders  Mage_Sales_Model_Resource_Order_Collection */
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
                $orders->getSelect()->having('COUNT(DISTINCT main_table.entity_id) > 1');
            }

            if ($advfilter->getData('from_store') || $advfilter->getData('item_sku')) {
                $addOrders = true;
                $orders->join(array('oi' => $orders->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array());

                if ($advfilter->getData('from_store')) {
                    $orders->addFieldToFilter('oi.source_store_view', $advfilter->getData('from_store'));
                }

                if ($advfilter->getData('item_sku')) {
                    $orders->addFieldToFilter('oi.sku', array('like' =>
                        $helper->addLikeEscape($this->getValue(), array('position' => 'any'))));
                }
            }

            if ($addOrders) {
                $collection->getSelect()->join(array('o' => $orders->getSelect()), 'o.customer_id = e.entity_id', array());
            }
        }

        return parent::setCollection($collection);
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Customer_Grid::_prepareColumns()
     * by customizing the rendered columns
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('billing_company', array(
            'header'    => Mage::helper('customer')->__('Company'),
            'index'     => 'billing_company'
        ), 'email');

        $this->addColumnAfter('billing_city', array(
            'header'    => Mage::helper('customer')->__('City'),
            'width'     => '90',
            'index'     => 'billing_city',
        ), 'Telephone');

        parent::_prepareColumns();

        $this->removeColumn('entity_id')
            ->removeColumn('billing_country_id')
            ->removeColumn('website_id')
            ->removeColumn('customer_since');
        $this->getColumn('action')->unsWidth();
        return $this;
    }
}
