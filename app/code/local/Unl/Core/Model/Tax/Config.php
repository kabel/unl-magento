<?php

class Unl_Core_Model_Tax_Config extends Mage_Tax_Model_Config
{
    const CONFIG_XML_PATH_EXEMPT_SHIPPING_TAX_CLASS = 'tax/classes/exempt_shipping_tax_class';
    const CONFIG_XML_PATH_FALLBACK_ZIP = 'tax/defaults/fallback_zip';

    /**
     * Get tax class id specified for shipping tax estimation
     *
     * @param   mixed $store
     * @return  int
     */
    public function getExemptShippingTaxClass($store = null)
    {
        return (int)Mage::getStoreConfig(self::CONFIG_XML_PATH_EXEMPT_SHIPPING_TAX_CLASS, $store);
    }

    /**
     * Get the zip code to use when boundary searching fails
     *
     * @param mixed $store
     * @return mixed
     */
    public function getFallbackZip($store = null)
    {
        return Mage::getStoreConfig(self::CONFIG_XML_PATH_FALLBACK_ZIP, $store);
    }
}
