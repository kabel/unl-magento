<?php

class Unl_Ship_Block_Shipment_Create_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
	/**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Prepare child blocks
     */
    protected function _beforeToHtml()
    {
        if ($this->isShippingCarrierSupported()) {
            $this->setChild(
                'submit_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                    'label'     => Mage::helper('sales')->__('Submit Shipment'),
                    'class'     => 'save submit-button',
                    'onclick'   => 'editFormPreSubmit()',
                ))
            );
        }

        return parent::_beforeToHtml();
    }

    public function formatPrice($price)
    {
        return $this->getOrder()->formatPrice($price);
    }

    public function canSendShipmentEmail()
    {
        return Mage::helper('sales')->canSendNewShipmentEmail($this->getOrder()->getStore()->getId());
    }

    public function isShippingCarrierSupported()
    {
        return Mage::helper('unl_ship')->isOrderSupportAutoShip($this->getOrder());
    }
}
