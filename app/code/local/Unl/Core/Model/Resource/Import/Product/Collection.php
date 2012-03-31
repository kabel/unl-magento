<?php

class Unl_Core_Model_Resource_Import_Prodcut_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    public function getExtendedInfo($columns, $attributes)
    {
        $this->getSelect()->reset(Zend_Db_Select::COLUMNS)
            ->columns($columns);

        $this->addAttributeToSelect($attributes, 'left');

        return $this->getConnection()->fetchAll($this->getSelect());
    }
}
