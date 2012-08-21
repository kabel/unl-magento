<?php

class Unl_Ship_Block_Update extends Mage_Adminhtml_Block_Abstract
{
    public function addQueueButtons()
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_Shipment_Create */
        $block = $this->getParentBlock();

        if (!Mage::helper('unl_ship')->isUnlShipQueueEmpty()) {
            $block->addButton('queue_next', array(
                'label'     => Mage::helper('adminhtml')->__('Next in Queue'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/nextInQueue')}')",
            ));
            $block->addButton('queue_clear', array(
                'label'     => Mage::helper('adminhtml')->__('Clear Queue'),
                'onclick'   => "setLocation('{$this->getUrl('*/*/clearQueue')}')",
            ));
        }
    }

    public function addVoidButton()
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_Shipment_View */
        $block = $this->getParentBlock();

        $shipment = $block->getShipment();
        $carrier  = $shipment->getOrder()->getShippingCarrier();
        if ($this->_isAllowedSalesAction('void_shipment') && $carrier instanceof Unl_Ship_Model_Shipping_Carrier_VoidInterface
            && $carrier->isVoidAvailable()
        ) {
            $block->addButton('void', array(
                'label'     => Mage::helper('adminhtml')->__('Void'),
                'class'     => 'delete',
                'onclick'   => 'deleteConfirm(\''. Mage::helper('adminhtml')->__('This will permanently remove this shipment and void the associated tracking numbers. Are you sure you want to do this?')
                .'\', \'' . $this->getUrl('*/*/void', array('shipment_id' => $shipment->getId())) . '\')',
            ));
        }
    }

    protected function _isAllowedSalesAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
