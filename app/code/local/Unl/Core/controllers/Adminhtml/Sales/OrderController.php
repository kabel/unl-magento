<?php

require_once "Mage/Adminhtml/controllers/Sales/OrderController.php";

class Unl_Core_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * Back out (cancel) $0 invoiced order
     */
    public function backOutAction()
    {
        if ($order = $this->_initOrder()) {
            try {
                $order->backOut()
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The order has been backed out.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('The order has not been backed out.'));
                Mage::logException($e);
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }
}
