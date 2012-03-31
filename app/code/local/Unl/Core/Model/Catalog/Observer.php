<?php

class Unl_Core_Model_Catalog_Observer
{
    /**
     * Unl_Core main helper
     *
     * @var Unl_Core_Helper_Data
     */
    protected $_helper;

    /**
     * Returns the local reference to the unl_core helper
     *
     * @return Unl_Core_Helper_Data
     */
    protected function _getHelper()
    {
        if (null === $this->_helper) {
            $this->_helper = Mage::helper('unl_core');
        }

        return $this->_helper;
    }

    /**
     * An event observer for the <code>catalog_controller_category_init_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Catalog_Observer
     */
    public function isCustomerAllowedCategory($observer)
    {
        $cat    = $observer->getEvent()->getCategory();
        $action = $observer->getEvent()->getControllerAction();
        $result = $observer->getEvent()->getResult();
        if (!$this->_getHelper()->isCustomerAllowedCategory($cat, true, false, $action)) {
            $result->setPreventDefault(true);
        }

        return $this;
    }

    /**
     * An event observer for the <code>catalog_controller_product_init_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Catalog_Observer
     */
    public function isCustomerAllowedProduct($observer)
    {
        $prod   = $observer->getEvent()->getProduct();
        $action = $observer->getEvent()->getControllerAction();
        $result = $observer->getEvent()->getResult();
        if (!$this->_getHelper()->isCustomerAllowedProduct($prod, $action)) {
            $result->setPreventDefault(true);
        }

        return $this;
    }

    /**
     * An event observer for the <code>catalog_product_is_salable_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Catalog_Observer
     */
    public function isProductNoSale($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $result  = $observer->getEvent()->getSalable();

        if ($product->getNoSale() !== null) {
            $result->setIsSalable($result->getIsSalable() && !$product->getNoSale());
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the custom
     * <code>catalog_product_collection_render_filters_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Catalog_Observer
     */
    public function beforeRenderProductCollectionFilters($observer)
    {
        if (Mage::registry('UNL_PRODUCT_GRID')) {
            $collection = $observer->getEvent()->getCollection();
            Mage::helper('unl_core')->addProductAdminScopeFilters($collection);
        }

        return $this;
    }
}
