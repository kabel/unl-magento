<?php

class Unl_Inventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getQtyOnHand($product)
    {
        if (is_numeric($product)) {
            $productId = $product;
        } else {
            $productId = $product->getId();
        }

        $resource = Mage::getResourceSingleton('unl_inventory/purchase');

        $qty = $resource->loadQtyOnHand($productId);
        if ($qty) {
            return $qty;
        }

        return 0;
    }

    public function getProductPurchaseCost($product)
    {
        return Mage::getSingleton('unl_inventory/change')->getProductPurchaseCost($product);
    }

    public function productHasAudits($product)
    {
        $collection = Mage::getResourceModel('unl_inventory/audit_collection')
            ->addProductFilter($product);

        return $collection->getSize() > 0;
    }

    public function isAllowedInventoryEdit()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/inventory/edit');
    }

    public function getIsAuditInventory($product, $fromOriginal = false, $fromStockData = false)
    {
        if ($fromOriginal && !$product->getId()) {
            return false;
        }

        $configStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        if ($fromStockData && ($stockData = $product->getStockData()) && isset($stockData['use_config_manage_stock'])) {
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
