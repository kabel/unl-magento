<?php

class Unl_Core_Model_Warehouse_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = false)
    {
        if (is_null($this->_options)) {
            $this->_options = Mage::getResourceModel('unl_core/warehouse_collection')
                ->load()
                ->toOptionArray();
        }

        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>''));
        }
        return $options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}