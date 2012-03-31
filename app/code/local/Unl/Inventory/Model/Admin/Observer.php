<?php

class Unl_Inventory_Model_Admin_Observer
{
    /**
     * An <i>adminhtml</i> event observer for the custom
     * <code>unl_inventory_controller_product_init</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Admin_Observer
     */
    public function onInventoryProductInit($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $flags = $observer->getEvent()->getFlags();

        if ($product->getId()) {
            if (!Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
                $flags->setDenied(true);
            }
        }

        return $this;
    }
}
