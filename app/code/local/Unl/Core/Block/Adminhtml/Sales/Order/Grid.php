<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Order_Grid::setCollection()
     * by adding extra cols and filters
     */
    public function setCollection($collection)
    {
        /* @var $collection Mage_Sales_Model_Resource_Order_Grid_Collection */
        $helper = Mage::getResourceHelper('core');
        $adapter = $collection->getConnection();

        $collection
            ->addFilterToMap('increment_id', 'main_table.increment_id')
            ->addFilterToMap('created_at', 'main_table.created_at')
            ->addFilterToMap('billing_name', 'main_table.billing_name')
            ->addFilterToMap('shipping_name', 'main_table.shipping_name')
            ->addFilterToMap('base_grand_total', 'main_table.base_grand_total')
            ->addFilterToMap('status', 'main_table.status')
            ->addFilterToMap('external_id', 'o.external_id');

        $collection->join(
            array('o' => 'sales/order'),
            'main_table.entity_id = o.entity_id',
            array('external_id')
        );

        /* @var $advfilter Varien_Object */
        $advfilter = Mage::helper('unl_core')->getAdvancedGridFilters('order');

        if (!empty($advfilter) && $advfilter->hasData()) {
            $storeIds = null;
            if ($advfilter->getData('source_store')) {
                $storeIds = $advfilter->getData('source_store');
            }

            $select = Mage::helper('unl_core')->addAdminScopeFilters($collection, 'entity_id', true, $storeIds);

            if ($advfilter->getData('shipping_method')) {
                $collection->addFieldToFilter('o.shipping_description',
                    array('like' => $helper->addLikeEscape($advfilter->getData('shipping_method')))
                );
            }

            if ($advfilter->getData('item_sku') ||
                ($advfilter->hasData('can_ship') && $advfilter->getData('can_ship') !== '')
            ) {
                if (!$select) {
                    /* @var $select Varien_Db_Select */
                    $select = Mage::getModel('sales/order_item')->getCollection()->getSelect()
                        ->reset(Zend_Db_Select::COLUMNS)
                        ->columns(array('order_id'))
                        ->group('order_id');
                    $collection->getSelect()->join(array('scope' => $select), 'main_table.entity_id = scope.order_id', array());
                }

                if ($advfilter->getData('item_sku')) {
                    $select->where($adapter->prepareSqlCondition('sku',
                        array('like' => $helper->addLikeEscape($advfilter->getData('item_sku')))
                    ));
                }

                if ($advfilter->hasData('can_ship') && $advfilter->getData('can_ship') !== '') {
                    if ($advfilter->getData('can_ship')) {
                        $collection->addFieldToFilter('o.state', array('nin' => array(
                            Mage_Sales_Model_Order::STATE_CANCELED,
                            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
                            Mage_Sales_Model_Order::STATE_HOLDED,
                            Mage_Sales_Model_Order::STATE_CLOSED,
                            Mage_Sales_Model_Order::STATE_COMPLETE,
                        )));
                        $collection->addFieldToFilter('is_virtual', false);

                        $cond = '>';
                    } else {
                        $cond = '=';
                    }

                    $check = $adapter->getGreatestSql(array(
                        'qty_ordered - qty_shipped - qty_refunded - qty_canceled',
                        0
                    ));
                    $cond = sprintf('SUM(%s) %s 0', $adapter->getCheckSql('is_virtual', '0', $check), $cond);
                    $select->having($cond);
                }
            }

            if ($advfilter->getData('payment_method')) {
                // we assume there is only one payment for an order!
                $collection->getSelect()
                    ->join(
                        array('p' => $collection->getTable('sales/order_payment')),
                        'main_table.entity_id = p.parent_id',
                        array()
                    );

                $collection->addFieldToFilter('p.method', $advfilter->getData('payment_method'));
            }

            if ($advfilter->hasData('has_tax') && $advfilter->getData('has_tax') !== '') {
                if ($advfilter->getData('has_tax')) {
                    $cond = 'gt';
                } else {
                    $cond = 'eq';
                }
                $collection->addFieldToFilter('o.base_tax_amount', array($cond => 0));
            }
        }  else {
            Mage::helper('unl_core')->addAdminScopeFilters($collection, 'entity_id', true);
        }

        return parent::setCollection($collection);
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Sales_Order_Grid::_prepareColumns()
     * by changing displayed columns
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('external_id', array(
            'header' => Mage::helper('sales')->__('External #'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'external_id'
        ), 'real_order_id');

        parent::_prepareColumns();

		$this->removeColumn('store_id');
		$this->getColumn('status')->setWidth('110px');
		$this->getColumn('created_at')->setWidth('160px');
		$this->getColumn('base_grand_total')->setHeader(Mage::helper('sales')->__('G.T.'));
        $this->removeColumn('grand_total');

        return $this;
    }
}
