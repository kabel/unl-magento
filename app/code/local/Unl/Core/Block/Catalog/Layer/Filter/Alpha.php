<?php

class Unl_Core_Block_Catalog_Layer_Filter_Alpha extends Mage_Catalog_Block_Layer_Filter_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->_filterModelName = 'unl_core/catalog_layer_filter_alpha';
    }
}
