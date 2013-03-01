<?php

class Unl_Inventory_Model_Backorder extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_inventory/backorder');
    }

    /**
     * Reduces the quantity of this backordered item and updates the necessary
     * invoice items, order items, and audits, relinking to new purchase as necessary
     *
     * @param Unl_Inventory_Model_Purchase $purchase
     * @param float $qty
     * @param float $amount
     */
    public function handlePurchase($purchase, $qty, $amount)
    {
        Mage::throwException('Not implemented yet');
        //TODO: Fetch associations (invoice items, audits) and update (re-link to purchase), then delete if qty covered
    }
}
