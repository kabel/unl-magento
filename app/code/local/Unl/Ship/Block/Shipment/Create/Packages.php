<?php

class Unl_Ship_Block_Shipment_Create_Packages extends Mage_Adminhtml_Block_Template
{
	/**
     * Prepares layout of block
     *
     */
    protected function _prepareLayout()
    {
        $this->setChild('add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'   => Mage::helper('sales')->__('Add Package'),
                    'class'   => 'add-package-button',
                    'onclick' => 'packageControl.add()'
                ))

        );

        return parent::_prepareLayout();
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

    public function getContainers()
    {
        $model = null;
        $carrierCode = $this->getOrder()->getShippingCarrier()->getCarrierCode();
        switch ($carrierCode) {
            case 'ups':
                $model = Mage::getModel('usa/shipping_carrier_ups_source_container');
                break;
            case 'fedex':
                $model = Mage::getModel('usa/shipping_carrier_fedex_source_packaging');
                break;
        }

        if ($model === null) {
            return array();
        }

        return $model->toOptionArray();
    }

    public function getContainerDimensions()
    {
        $carrier = $this->getOrder()->getShippingCarrier();
        if ($carrier->getCarrierCode() == 'fedex') {
            return $carrier->getCode('package_dimensions_' . strtolower($carrier->getDimensionUnits()));
        } elseif ($carrier->getCarrierCode() == 'ups') {
            return $carrier->getCode('container_dimensions_' . strtolower($carrier->getDimensionUnits()));
        } else {
            return array();
        }
    }
}
