<?php

class Unl_Core_Model_Tax_Config extends Mage_Tax_Model_Config
{
    const CONFIG_XML_PATH_EXEMPT_SHIPPING_TAX_CLASS = 'tax/classes/exempt_shipping_tax_class';

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param   store $store
     * @return  int
     */
    public function getExemptShippingTaxClass($store=null)
    {
        return (int)Mage::getStoreConfig(self::CONFIG_XML_PATH_EXEMPT_SHIPPING_TAX_CLASS, $store);
    }
}
