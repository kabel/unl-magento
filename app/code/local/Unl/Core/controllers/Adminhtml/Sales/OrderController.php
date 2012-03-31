<?php

require_once 'Mage/Adminhtml/controllers/Sales/OrderController.php';

class Unl_Core_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /* Overrides
     * @see Mage_Adminhtml_Sales_OrderController::addressAction()
     * by not loading the entire address collection
     */
    public function addressAction()
    {
        $addressId = $this->getRequest()->getParam('address_id');
        $address = Mage::getModel('sales/order_address')->load($addressId);
        if ($address->getId()) {
            Mage::register('order_address', $address);
            $this->loadLayout();
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
        }
    }
}
