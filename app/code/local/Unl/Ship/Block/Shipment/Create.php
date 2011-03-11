<?php

class Unl_Ship_Block_Shipment_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'order_id';
        $this->_blockGroup = 'unl_ship';
        $this->_controller = 'shipment';
        $this->_mode = 'create';

        parent::__construct();

        $this->_removeButton('save');
        $this->_removeButton('delete');

        if (!$this->isQueueEmpty()) {
            $this->_addButton('queue_next', array(
                'label'     => Mage::helper('adminhtml')->__('Next in Queue'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/nextInQueue')}')",
            ));
            $this->_addButton('queue_clear', array(
                'label'     => Mage::helper('adminhtml')->__('Clear Queue'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/clearQueue')}')",
            ));
        }
    }

    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getHeaderText()
    {
        $header = Mage::helper('sales')->__('New Shipment for Order #%s', $this->getOrder()->getRealOrderId());
        return $header;
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$this->getOrder()->getId()));
    }

    public function isQueueEmpty()
    {
        return Mage::helper('unl_ship')->isUnlShipQueueEmpty();
    }
}