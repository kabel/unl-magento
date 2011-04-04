<?php

class Unl_Inventory_Model_Source_Audittype
{
    public function toOptionArray($full = false)
    {
        $options = array(
            array(
                'value' => Unl_Inventory_Model_Audit::TYPE_PURCHASE,
                'label' => Mage::helper('unl_inventory')->__('Purchase')
            ),
            array(
                'value' => Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT,
                'label' => Mage::helper('unl_inventory')->__('Adjustment')
            ),
        );

        if ($full) {
            $options = array_merge($options, array(
                array(
                    'value' => Unl_Inventory_Model_Audit::TYPE_SALE,
                    'label' => Mage::helper('unl_inventory')->__('Sale')
                ),
                array(
                    'value' => Unl_Inventory_Model_Audit::TYPE_CREDIT,
                    'label' => Mage::helper('unl_inventory')->__('Credit')
                ),
            ));
        }

        return $options;
    }

    public function toOptionHash($full = false)
    {
        $hash = array();
        $options = $this->toOptionArray($full);
        foreach ($options as $option) {
            $hash[$option['value']] = $option['label'];
        }

        return $hash;
    }
}