<?php

class Unl_Inventory_Model_Source_Accounting
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Unl_Inventory_Model_Config::ACCOUNTING_LIFO,
                'label' => Mage::helper('unl_inventory')->__('LIFO')
            ),
            array(
                'value' => Unl_Inventory_Model_Config::ACCOUNTING_FIFO,
                'label' => Mage::helper('unl_inventory')->__('FIFO')
            ),
            array(
                'value' => Unl_Inventory_Model_Config::ACCOUNTING_AVG,
                'label' => Mage::helper('unl_inventory')->__('Moving Average')
            ),
        );
    }
}
