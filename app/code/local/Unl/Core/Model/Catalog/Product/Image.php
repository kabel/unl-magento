<?php

class Unl_Core_Model_Catalog_Product_Image extends Mage_Catalog_Model_Product_Image
{
    /* Overrides
     * @see Mage_Catalog_Model_Product_Image::getUrl()
     * by passing the $secure param to the getBaseUrl method
     */
    public function getUrl($secure = null)
    {
        $baseDir = Mage::getBaseDir('media');
        $path = str_replace($baseDir . DS, "", $this->_newFile);
        return Mage::getBaseUrl('media', $secure) . str_replace(DS, '/', $path);
    }
}
