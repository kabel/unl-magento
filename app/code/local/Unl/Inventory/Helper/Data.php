<?php

class Unl_Inventory_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getQtyOnHand($productId)
    {
        $resource = Mage::getResourceSingleton('unl_inventory/purchase');
        
        $qty = $resource->loadQtyOnHand($productId);
        if ($qty) {
            return $qty;
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
    
    public function productHasAudits($productId)
    {
        $collection = Mage::getResourceModel('unl_inventory/index_collection')
            ->addFieldToFilter('product_id', $productId);
        
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
