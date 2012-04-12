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

    protected function _isAllowedSalesAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
