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
        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        $collection = Mage::getResourceModel('unl_inventory/index_collection')
            ->addProductFilter($productId)
            ->addAccountingOrder($accounting)
            ->setPageSize(1);

        foreach ($collection as $index) {
            return $index->getAmount() / $index->getQtyOnHand();
        }

        return 0;
    }

    public function getIsAuditInventory($product, $fromOriginal = false, $fromStockData = false)
    {
        if ($fromOriginal && !$product->getId()) {
            return false;
        }

        $configStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        if ($fromStockData && $stockData = $product->getStockData()) {
            $manageStock = $stockData['use_config_manage_stock'] ? $configStock : (bool)$stockData['manage_stock'];
        } else {
            $stockData = $product->getStockItem();
            if (!$stockData) {
                return false;
            }
            $manageStock = $stockData->getManageStock();
        }

        if ($fromOriginal) {
            return $manageStock && $product->getOrigData('audit_inventory');
        }

        return $manageStock && $product->getAuditInventory();
    }
}
