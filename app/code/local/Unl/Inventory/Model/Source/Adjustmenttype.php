<?php

class Unl_Inventory_Model_Source_Adjustmenttype
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT_OFFSET,
                'label' => Mage::helper('unl_inventory')->__('Offset')
            ),
            array(
                'value' => Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT_SET,
                'label' => Mage::helper('unl_inventory')->__('Set')
            ),
        );
    }

    public function toOptionHash()
    {
        $hash = array();
        $options = $this->toOptionArray();
        foreach ($options as $option) {
            $hash[$option['value']] = $option['label'];
        }

        return $hash;
    }
}