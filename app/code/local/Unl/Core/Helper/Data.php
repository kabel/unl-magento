<?php

class Unl_Core_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function fetchServerFile($path)
    {
        return file_get_contents('http://' . Mage::helper('core/http')->getHttpHost() . $path);
    }
}