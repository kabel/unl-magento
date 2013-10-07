<?php

class Unl_Core_Helper_Cms_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{
    public function getBaseUrl()
    {
        return Mage::getBaseUrl('media') . Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY . '/';
    }
}
