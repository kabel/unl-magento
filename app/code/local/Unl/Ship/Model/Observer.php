<?php

class Unl_Ship_Model_Observer
{
    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();

        //Do actions based on block type

        $type = 'Mage_Adminhtml_Block_Sales_Order_Grid';
        if ($block instanceof $type) {
            if ($this->_isAllowedSalesAction('label_ship'))  {
                $block->getMassactionBlock()->addItem('unl_ship_queue', array(
                     'label'=> Mage::helper('sales')->__('Queue for Auto Ship'),
                     'url'  => $block->getUrl('unl_ship/index/queueOrders'),
                ));
            }
            return;
        }

        $type = 'Mage_Adminhtml_Block_Sales_Order_View';
        if ($block instanceof $type) {
            if ($this->_isAllowedSalesAction('label_ship') && $block->getOrder()->canShip()
                && Mage::helper('unl_ship')->isOrderSupportAutoShip($block->getOrder())) {
                $block->addButton('auto_ship', array(
                    'label'     => Mage::helper('sales')->__('Auto Ship'),
                    'onclick'   => "setLocation('{$block->getUrl('unlship/')}')",
                    'class'     => 'go',
                ));
            }
            return;
        }
    }

    protected function _isAllowedSalesAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
