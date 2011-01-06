<?php

class Unl_Core_Block_Adminhtml_Customer_Grid extends Mage_Adminhtml_Block_Customer_Grid
{
    protected function _prepareCollection()
    {
        // FROM PARENT
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

        $advfilter = Mage::getSingleton('adminhtml/session')->getData('customerGridadvfilter');
        if (!empty($advfilter) && $advfilter->hasData()) {
            /* @var $orders  Mage_Sales_Model_Mysql4_Order_Collection */
            $orders = Mage::getModel('sales/order')->getResourceCollection();

            if ($advfilter->getData('purchase_from') || $advfilter->getData('purchase_to')) {
                 $orders->addFieldToFilter('main_table.created_at', array(
                 	'from' => $advfilter->getData('purchase_from'),
                    'to' => $advfilter->getData('purchase_to'))
                 );
            }

            if ($advfilter->getData('from_store') || $advfilter->getData('item_sku')) {
                $orders->getSelect()->join(array('oi' => $orders->getTable('sales/order_item')), 'main_table.entity_id = oi.order_id', array());
                if ($advfilter->getData('from_store')) {
                    $orders->getSelect()->where('oi.source_store_view = ?', $advfilter->getData('from_store'));
                }

                if ($advfilter->getData('item_sku')) {
                    $orders->getSelect()->where('oi.sku LIKE ?', $advfilter->getData('item_sku') . '%');
                }
            }

            $orders->getSelect()
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('customer_id'))
                ->group('customer_id');

            $collection->getSelect()->join(array('o' => $orders->getSelect()), 'o.customer_id = e.entity_id', array());
        }

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}