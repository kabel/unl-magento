<?php

class Unl_Inventory_Model_Observer
{
    protected $_configBeforeSave = array();

    public function onBeforeProductSave($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        /* @var $helper Unl_Inventory_Helper_Data */
        $helper = Mage::helper('unl_inventory');

        $isAudit = $helper->getIsAuditInventory($product, false, true);
        $wasAudit = $helper->getIsAuditInventory($product, true);
        // if auditing inventory, disable changing cost
        if ($isAudit && !$product->hasCostFlag()) {
            $product->setCost($product->getOrigData('cost'));
        }

        if (!$wasAudit && $isAudit) {
            $stockData = $product->getStockData();
            if ($product->getId()) {
                $product->setCost($helper->getIndexProductCost($product->getId()));
                $stockData['qty'] = $helper->getQtyOnHand($product->getId());

                $this->_logAuditNote($product->getId(), $helper->__('Inventory auditing has been restarted'));
            } else {
                $product->setCost(0);
                $stockData['qty'] = 0;
            }
            $tempStockItem = new Mage_CatalogInventory_Model_Stock_Item($stockData);
            $stockData['is_in_stock'] = $tempStockItem->verifyStock();
            $product->setStockData($stockData);
        } elseif ($wasAudit && !$isAudit) {
            $this->_logAuditNote($product->getId(), $helper->__('Inventory auditing has been stopped'));
        }

        return $this;
    }

    protected function _logAuditNote($product_id, $msg)
    {
        $auditLog = Mage::getModel('unl_inventory/audit');
        $auditLog->setData(array(
            'product_id' => $product_id,
            'type' => Unl_Inventory_Model_Audit::TYPE_NOTE_ONLY,
            'created_at' => now(),
            'note' => $msg,
        ));
        $auditLog->save();

        return $this;
    }

    public function onInvoiceRegister($observer)
    {
        /* TODO: Add to Audit as TYPE_SALE w/ note of Invoice #
         * FIFO/LIFO: deduct/delete inventory_index and amount, set order_item cost if spans indexes, update cost if delete
         * Moving Avg: deduct inventory_index and cost, update cost
         */
    }

    public function onCreditMemoRegister($observer)
    {
        /* TODO: (if return to stock!) Add to Audit as TYPE_CREDIT w/ note of memo #
         * FIFO/LIFO: If cost matches: add back to qty index; Else: add as next out index
         * Moving Avg: Add cost * qty to index amount and add qty, update cost
         */
    }

    public function onPredispatchSaveConfig($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        /* @var $controller Mage_Adminhtml_System_ConfigController */

        $section = $controller->getRequest()->getParam('section');
        if ($section == 'cataloginventory') {
            $this->_configBeforeSave['manage_stock'] = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
            $this->_configBeforeSave['accounting'] = Mage::getSingleton('unl_inventory/config')->getAccounting();
        }

        return $this;
    }

    public function onInventoryConfigChange($observer)
    {
        $updateCost = 0;

        $configManageStock = Mage::getStoreConfigFlag(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        if ($this->_configBeforeSave['manage_stock'] != $configManageStock) {
            $products = $this->_getAuditProductCollection();
            // batch of 10000
            $products->setPage(1, 10000)->setFlag('require_stock_items', true);
            if ($configManageStock) {
                $updateCost = 2;

                $products->getSelect()->joinLeft(
                    array('ii' => Mage::getModel('unl_inventory/index')->getCollection()->selectQtyOnHand()->getSelect()),
                    'e.entity_id = ii.product_id',
                    array('qty_on_hand' => 'IFNULL(ii.qty, 0)'));
            }
            $products->getSelect()->where('_table_qty.use_config_manage_stock');

            for ($i = 1; $i <= $products->getLastPageNumber(); $i++) {
                foreach ($products as $product) {
                    if ($configManageStock) {
                        $product->getStockItem()
                            ->setQty($product->getQtyOnHand())
                            ->save();

                        $msg = Mage::helper('unl_inventory')->__('Inventory auditing has been restarted');
                    } else {
                        $msg = Mage::helper('unl_inventory')->__('Inventory auditing has been stopped');
                    }
                    $this->_logAuditNote($product->getId(), $msg);
                }

                $products->clear();
                $products->setCurPage($i + 1);
            }
        }

        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        if ($this->_configBeforeSave['accounting'] != $accounting) {
            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
                Mage::getResourceSingleton('unl_inventory/index')->flattenIndexes();
            } else {
                Mage::getResourceSingleton('unl_inventory/index')->rebuildIndex();
            }

            $updateCost = 1;
        }

        if ($updateCost) {
            $products = $this->_getAuditProductCollection();
            $products->setPage(1, 10000);
            if ($updateCost == 1) {
                $products->getSelect()->where('IF(_table_qty.use_config_manage_stock, 1, _table_qty.manage_stock)');
            } else {
                $products->getSelect()->where('_table_qty.use_config_manage_stock');
            }

            $products->getSelect()->joinLeft(array('ix' => $products->getTable('unl_inventory/index_idx')), 'e.entity_id = ix.product_id', array())
                ->joinLeft(array('ii' => $products->getTable('unl_inventory/index')), 'ix.index_id = ii.index_id', array(
                    'new_cost' => '(ii.qty_on_hand / ii.amount)'
                ));

            for ($i = 1; $i <= $products->getLastPageNumber(); $i++) {
                foreach ($products as $product) {
                    $product->setCostFlag(true)
                        ->setCost($product->getNewCost())
                        ->save();
                }

                $products->clear();
                $products->setCurPage($i + 1);
            }
        }

        $catalogindexObserver = Mage::getSingleton('cataloginventory/observer');
        $catalogindexObserver->updateItemsStockUponConfigChange($observer);

        return $this;
    }

    /**
     *
     * @return Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection
     */
    protected function _getAuditProductCollection()
    {
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('cost')
            ->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left')
            ->addAttributeToFilter('audit_inventory', true);

        return $products;
    }
}