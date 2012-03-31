<?php

class Unl_Core_Model_Resource_Catalog_Layer_Filter_Alpha_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function getFilterAttributeAlias()
    {
        $this->addAttributeToSelect('name', 'left');
        return $this->_getAttributeFieldName('name');
    }
}
