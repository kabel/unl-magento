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
        if (!is_null($user->getScope())) {
            $scope = explode(',', $user->getScope());
            $order_items = Mage::getModel('sales/order_item')->getCollection();
            /* @var $order_items Mage_Sales_Model_Mysql4_Order_Item_Collection */
            $select = $order_items->getSelect()->reset(Zend_Db_Select::COLUMNS)
                ->columns(array('order_id'))
                ->where('source_store_view IN (?)', $scope)
                ->group('order_id');
                
            $collection->getSelect()
                ->joinInner(array('scope' => $select), 'main_table.entity_id = scope.order_id', array());
        }
        
        //MAYBE: Add payment method to grid
        
        $this->setCollection($collection);
        
        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
    
    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header'=> Mage::helper('sales')->__('Order #'),
            'width' => '80px',
            'type'  => 'text',
            'index' => 'increment_id',
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
            'type' => 'datetime',
            'width' => '150px',
        ));

        $this->addColumn('billing_name', array(
            'header' => Mage::helper('sales')->__('Bill to Name'),
            'index' => 'billing_name',
        ));

        $this->addColumn('shipping_name', array(
            'header' => Mage::helper('sales')->__('Ship to Name'),
            'index' => 'shipping_name',
        ));

        $this->addColumn('base_grand_total', array(
            'header' => Mage::helper('sales')->__('G.T. (Base)'),
            'index' => 'base_grand_total',
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
    
    protected function _getPaymentMethods()
    {
        $config = Mage::getModel('payment/config');
        $methods = $config->getAllMethods();
        
        1+1;
    }
}