<?php

class Unl_Core_Block_Catalog_Product_Featured_Grid extends Unl_Core_Block_Catalog_Product_Featured
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('catalog/product/featured_grid.phtml');
    }
}
