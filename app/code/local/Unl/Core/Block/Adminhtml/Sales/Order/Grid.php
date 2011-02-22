<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareCollection()
    {
        /* @var $collection Mage_Sales_Model_Mysql4_Order_Grid_Collection */
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $collection->getSelect()
            ->join(array('o' => $collection->getTable('sales/order')),
                'main_table.entity_id = o.entity_id',
                array('external_id')
            );

        $user  = Mage::getSingleton('admin/session')->getUser();
        $select = false;
        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            $select = $this->_getOrderItemSelect()->where('source_store_view IN (?)', $scope);
            if ($user->getWarehouseScope()) {
                $select->where('warehouse IN (?)', $user->getWarehouseScope());
            }

            $collection->getSelect()
                ->join(array('scope' => $select), 'main_table.entity_id = scope.order_id', array());
        }

        $advfilter = Mage::helper('unl_core')->getAdvancedGridFilters('order');
        if (!empty($advfilter) && $advfilter->hasData()) {
            if ($advfilter->getData('shipping_method')) {
                $collection->getSelect()->where('o.shipping_description LIKE ?', '%' . $advfilter->getData('shipping_method') . '%');
            }

            if ($advfilter->getData('item_sku')) {
                if (!$select) {
                    $select = $this->_getOrderItemSelect();
                    $collection->getSelect()
                        ->join(array('scope' => $select), 'main_table.entity_id = scope.order_id', array());
                }

                $select->where('sku LIKE ?', $advfilter->getData('item_sku') . '%');
            }

            if ($advfilter->getData('payment_method')) {
                /* @var $payment Mage_Sales_Model_Mysql4_Order_Payment_Collection */
                $payment = Mage::getModel('sales/order_payment')->getCollection();
                $payment->addFieldToFilter('method', array('eq' => $advfilter->getData('payment_method')));
                $payment->getSelect()
                    ->reset(Zend_Db_Select::COLUMNS)
                    ->columns(array('parent_id'))
                    ->group('parent_id');

                $collection->getSelect()
                    ->join(array('p' => $payment->getSelect()), 'main_table.entity_id = p.parent_id', array());
            }
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }

    protected function _getOrderItemSelect()
    {
        $order_items = Mage::getModel('sales/order_item')->getCollection();
        /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
        $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns(array('order_id'))
            ->group('order_id');

        return $select;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
            'filter_index' => 'main_table.increment_id',
        ));

        $this->addColumn('external_id', array(
            'header' => Mage::helper('sales')->__('External #'),
            'width'  => '80px',
            'type'   => 'text',
            'index'  => 'external_id'
        ));

        /*if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store_id', array(
                'header'    => Mage::helper('sales')->__('Purchased from (store)'),
                'index'     => 'store_id',
                'type'      => 'store',
                'store_view'=> true,
                'display_deleted' => true,
            ));
        }*/

        $this->addColumn('created_at', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'created_at',
            'filter_index' => 'main_table.created_at',
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
            'filter_index' => 'main_table.billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
            'filter_index' => 'main_table.shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
            'filter_index' => 'main_table.base_grand_total',
            'type'  => 'currency',
            'currency' => 'base_currency_code',
        ));

        /* THIS IS POINTLESS BECAUSE WE ONLY SUPPORT USD
        $this->addColumn('grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Purchased)'),
            'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
        ));
        */

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Status'),
            'index' => 'status',
            'filter_index' => 'main_table.status',
            'type'  => 'options',
            'width' => '70px',
            'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
        ));

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            $this->addColumn('action',
                array(
                    'header'    => Mage::helper('sales')->__('Action'),
                    'width'     => '50px',
                    'type'      => 'action',
                    'getter'     => 'getId',
                    'actions'   => array(
                        array(
                            'caption' => Mage::helper('sales')->__('View'),
                            'url'     => array('base'=>'*/*/view'),
                            'field'   => 'order_id'
                        )
                    ),
                    'filter'    => false,
                    'sortable'  => false,
                    'index'     => 'stores',
                    'is_system' => true,
            ));
        }
        $this->addRssList('rss/order/new', Mage::helper('sales')->__('New Order RSS'));

        $this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

        return Mage_Adminhtml_Block_Widget_Grid::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ship'))  {
            $this->getMassactionBlock()->addItem('ordership_queue', array(
                 'label'=> Mage::helper('sales')->__('Queue for Auto Ship'),
                 'url'  => $this->getUrl('ordership/index/queueorders'),
            ));
        }
    }

    /**
     * Add new rss list to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addRssList($url, $label)
    {
        $this->_rssLists[] = new Varien_Object(
            array(
                'url'   => Mage::getModel('core/url')->getUrl($url, array('_store' => 'default')),
                'label' => $label
            )
        );
        return $this;
    }
}