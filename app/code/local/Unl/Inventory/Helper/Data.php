<?php

class Unl_Inventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getQtyOnHand($productId)
    {
        $collection = Mage::getResourceModel('unl_inventory/index_collection')
            ->addProductFilter($productId)
            ->selectQtyOnHand();

        foreach ($collection as $row) {
            return $row->getQty();
        }

        return 0;
    }

    public function getIndexProductCost($productId)
    {
        $accounting = Mage::getModel('unl_inventory/config')->getAccounting();
        $collection = Mage::getResourceModel('unl_inventory/index_collection')
            ->addProductFilter($productId)
            ->addAccountingOrder($accounting);

        foreach ($collection as $index) {
            return $index->getAmount() / $index->getQtyOnHand();
        }

        return 0;
    }
}
