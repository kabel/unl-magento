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
        $audits = Mage::getResourceModel('unl_inventory/audit_collection');
        $audits->addBackorderFilter($this);

        /* @var $audit Unl_Inventory_Model_Audit */
        foreach ($audits as $audit) {
            $audit->setAmount($audit->getAmount() - $amount);
            $audit->syncItemCost();
            $audit->setPurchaseAssociations(array(
                array('purchase' => $purchase, 'qty' => $qty)
            ));
            break;
        }

        $this->setQty($this->getQty() - $qty);
        if ($this->getQty() <= 0) {
            $this->isDeleted(true);
        }

        $this->save();
    }
}
