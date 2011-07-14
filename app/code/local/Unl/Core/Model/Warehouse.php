<?php

class Unl_Core_Model_Warehouse  extends Mage_Core_Model_Abstract
{
    protected $_filterStates = array(
        Mage_Sales_Model_Order::STATE_NEW,
        Mage_Sales_Model_Order::STATE_HOLDED,
        Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,
        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
    );

	protected function _construct()
    {
        $this->_init('unl_core/warehouse');
    }

    public function getFilterStates()
    {
        return $this->_filterStates;
    }
}
