<?php

class Unl_Inventory_Model_Config
{
    const ACCOUNTING_LIFO = 1;
    const ACCOUNTING_FIFO = 2;
    const ACCOUNTING_AVG  = 3;

    const XML_PATH_ACCOUNTING = 'cataloginventory/options/accounting';

    public function getAccounting()
    {
        return Mage::getStoreConfig(self::XML_PATH_ACCOUNTING);
    }
}
