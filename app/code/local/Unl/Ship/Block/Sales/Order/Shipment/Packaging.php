<?php

class Unl_Ship_Block_Sales_Order_Shipment_Packaging extends Mage_Adminhtml_Block_Sales_Order_Shipment_Packaging
{
    public function getPrintButton()
    {
        $data['shipment_id'] = $this->getShipment()->getId();
        $url = $this->getUrl('*/sales_order_shipment/printPackage', $data);
        return $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'   => Mage::helper('sales')->__('Print'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class'   => 'save',
            ))
            ->toHtml();
    }

    /**
     * Retrieve a collection of packages for the current shipment
     *
     * @return Unl_Ship_Model_Resource_Shipment_Package_Collection
     */
    public function getAdditionalPackages()
    {
        if (is_null($this->getShipment()->getAddlPackages())) {
            Mage::helper('unl_ship')->loadUnlPackages($this->getShipment());
        }

        return $this->getShipment()->getAddlPackages();
    }
}
