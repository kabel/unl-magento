<?php

class Unl_Inventory_Model_Observer
{
    protected $_configBeforeSave = array();

    /**
     * An event observer for the <code>catalog_product_save_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
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

                $collection = Mage::getModel('unl_inventory/audit')
                    ->getCollection()
                    ->addFieldToFilter('product_id', $product->getId());

                if ($collection->getSize()) {
                    $this->_logAuditNote($product->getId(), $helper->__('Inventory auditing has been restarted'));
                }
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

    /**
     * Instantiates and saves an audit log note
     *
     * @param int $product_id
     * @param string $msg
     * @return Unl_Inventory_Model_Observer
     */
    protected function _logAuditNote($product_id, $msg)
    {
        $auditLog = Mage::getModel('unl_inventory/audit');
        $auditLog->setData(array(
            'product_id' => $product_id,
            'type' => Unl_Inventory_Model_Audit::TYPE_NOTE_ONLY,
            'created_at' => Mage::getSingleton('core/date')->gmtDate(),
            'note' => $msg,
        ));
        $auditLog->save();

        return $this;
    }

    /**
     * An event observer for the <code>sales_model_service_order_prepare_invoice</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
    public function onPrepareInvoice($observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getEvent()->getOrder();
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
        $auditLogs = array();

        foreach ($invoice->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Invoice_Item */
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($item->getOrderItem()->isDummy() || !Mage::helper('unl_inventory')->getIsAuditInventory($product)) {
                continue;
            }

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_SALE,
                'qty' => $item->getQty() * -1,
                'amount' => $item->getQty() * $item->getBaseCost() * -1
            ));
            $auditLogs[] = $auditLog;

            // TODO?: Check for backordered items and reassign cost

            // check to see if we need to update the order item product cost (if spans multiple indexes)
            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
                continue;
            }

            $indexes = Mage::getResourceModel('unl_inventory/index_collection')
                ->addProductFilter($product->getId())
                ->addAccountingOrder($accounting)
                ->load();

            $costAmount = 0;
            $qty = $item->getQty();
            foreach ($indexes as $index) {
                if ($qty > $index->getQtyOnHand()) {
                    $costAmount += $index->getAmount();
                    $qty -= $index->getQtyOnHand();
                } else {
                    if ($costAmount) {
                        $costAmount += $index->getAmount() / $index->getQtyOnHand() * $qty;
                    }
                    break;
                }
            }

            if ($costAmount) {
                $cost = $costAmount / $item->getQty();
                $item->setBaseCost($cost);
                $item->setCost($cost * $order->getBaseToOrderRate());
                $item->getOrderItem()->setBaseCost($cost);
                $item->getOrderItem()->setCost($cost * $order->getBaseToOrderRate());
            } else {
                $costAmount = $item->getQty() * $item->getBaseCost();
            }

            $auditLog->setAmount($costAmount * -1);
        }

        if ($auditLogs) {
            $invoice->setAuditLogs($auditLogs);
        }

        return $this;
    }

    /**
     * An event observer for the <code>sales_order_invoice_cancel</code> event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
    public function onCancelInvoice($observer)
    {
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();

        $auditLogs = array();

        foreach ($invoice->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Invoice_Item */
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($item->getOrderItem()->isDummy()
                || !Mage::helper('unl_inventory')->getIsAuditInventory($product) ) {
                continue;
            }

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_CREDIT,
                'qty' => $item->getQty(),
                'amount' => $item->getBaseCost() * $item->getQty()
            ));
            $auditLogs[] = $auditLog;
        }

        if ($auditLogs) {
            $invoice->setAuditLogs($auditLogs);
        }

        return $this;
    }

    /**
     * An event observer for the <code>sales_order_invoice_save_after</code> event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
    public function onAfterSaveInvoice($observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($auditLogs = $invoice->getAuditLogs()) {
            $note = Mage::helper('unl_inventory')->__('Order # %s , Invoice # %s', $invoice->getOrder()->getRealOrderId(), $invoice->getIncrementId());
            $now = Mage::getSingleton('core/date')->gmtDate();
            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_CANCELED) {
                $note .= ' [' . Mage::helper('unl_inventory')->__('CANCELED') . ']';
            }
            foreach ($auditLogs as $auditLog) {
                $auditLog->setRegisterFlag(true)
                    ->setNote($note)
                    ->setCreatedAt($now)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * An event observer for the <code>adminhtml_sales_order_creditmemo_register_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
    public function onBeforeCreditmemoRegistry($observer)
    {
    	/* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $auditLogs = array();

        foreach ($creditmemo->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Creditmemo_Item */
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($item->getOrderItem()->isDummy() || !$item->getBackToStock()
                || !Mage::helper('unl_inventory')->getIsAuditInventory($product) ) {
                continue;
            }

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_CREDIT,
                'qty' => $item->getQty(),
                'amount' => $item->getBaseCost() * $item->getQty()
            ));
            $auditLogs[] = $auditLog;
        }

        if ($auditLogs) {
            $creditmemo->setAuditLogs($auditLogs);
        }

        return $this;
    }

    /**
     * An event observer for the <code>sales_order_creditmemo_save_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
    public function onAfterSaveCreditmemo($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($auditLogs = $creditmemo->getAuditLogs()) {
            $note = Mage::helper('unl_inventory')->__('Order # %s , Creditmemo # %s', $creditmemo->getOrder()->getRealOrderId(), $creditmemo->getIncrementId());
            $now = Mage::getSingleton('core/date')->gmtDate();
            foreach ($auditLogs as $auditLog) {
                $auditLog->setRegisterFlag(true)
                    ->setNote($note)
                    ->setCreatedAt($now)
                    ->save();
            }
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the
     * <code>controller_action_predispatch_adminhtml_system_config_save</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
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

    /**
     * An <i>adminhtml</i> event observer for the
     * <code>admin_system_config_changed_section_cataloginventory</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Inventory_Model_Observer
     */
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
     * @return Mage_Catalog_Model_Resource_Product_Collection
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
