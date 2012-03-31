<?php

class Unl_Ship_Block_Update extends Mage_Adminhtml_Block_Abstract
{
    public function updateParentButtons()
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_View */
        $block = $this->getParentBlock();
        $order = $block->getOrder();

        if ($this->_isAllowedSalesAction('label_ship') && $order->canShip()
            && Mage::helper('unl_ship')->isOrderSupportAutoShip($order)
        ) {
            $block->addButton('auto_ship', array(
                'label'     => Mage::helper('unl_ship')->__('Auto Ship'),
                'onclick'   => "setLocation('{$this->getUrl('*/sales_order_package/')}')",
                'class'     => 'go',
            ));
        }

        return $this;
    }

    protected function _isAllowedSalesAction($action) {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/' . $action);
    }
}
