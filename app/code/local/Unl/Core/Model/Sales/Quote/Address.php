<?php

class Unl_Core_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address
{
    /**
     * Fires an event after the shipping_method data member is set
     *
     * @param string $method
     * @return Unl_Core_Model_Sales_Quote_Address
     */
    public function setShippingMethod($method)
    {
        parent::setShippingMethod($method);
        Mage::dispatchEvent(
            $this->_eventPrefix . '_set_shipping_method_after',
            array(
                $this->_eventObject => $this
            )
        );

        return $this;
    }
}
