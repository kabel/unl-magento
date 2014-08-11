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
                $product->setCost($helper->getProductPurchaseCost($product));
                $stockData['qty'] = $helper->getQtyOnHand($product);

                if ($helper->productHasAudits($product)) {
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
    protected function _logAuditNote($productId, $msg)
    {
        $auditLog = Mage::getModel('unl_inventory/audit');
        $auditLog->setData(array(
            'product_id' => $productId,
            'type' => Unl_Inventory_Model_Audit::TYPE_NOTE_ONLY,
            'created_at' => Mage::getSingleton('core/date')->gmtDate(),
            'note' => $msg,
        ));
        $auditLog->save();

        return $this;
    }

    /**
     * Returns the auditable qty for an invoice item
     *
     * @param Mage_Sales_Model_Order_Invoice $invoice
     * @param Mage_Sales_Model_Order_Invoice_Item $item
     */
    protected function _getInvoiceItemAuditQty($invoice, $item)
    {
        $baseQty = $item->getQty();
        if ($item->getOrderItem()->isDummy()) {
            $baseQty = 1;
        }

        $parentOrderId = $item->getOrderItem()->getParentItemId();
        $parentItem = false;
        foreach ($invoice->getAllItems() as $tmpItem) {
            if ($tmpItem->getOrderItemId() == $parentOrderId) {
                $parentItem = $tmpItem;
                break;
            }
        }

        return $parentItem ? ($parentItem->getQty() * $baseQty) : $baseQty;
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
        /* @var $invoice Mage_Sales_Model_Order_Invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $auditLogs = array();

        foreach ($invoice->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Invoice_Item */
            if ($item->getQty() <= 0) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if (!Mage::helper('unl_inventory')->getIsAuditInventory($product)) {
                continue;
            }

            $qty = $this->_getInvoiceItemAuditQty($invoice, $item);

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_SALE,
                'qty' => $qty * -1,
                'amount' => $qty * $item->getBaseCost() * -1,
                'invoice_item' => $item,
            ));
            $auditLogs[] = $auditLog;
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
            if ($item->getQty() <= 0) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if (!Mage::helper('unl_inventory')->getIsAuditInventory($product)) {
                continue;
            }

            $qty = $this->_getInvoiceItemAuditQty($invoice, $item);

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_CREDIT,
                'qty' => $qty,
                'amount' => $qty * $item->getBaseCost(),
                'invoice_item' => $item,
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
            $invoice->unsAuditLogs();
            $note = Mage::helper('unl_inventory')->__('Order # %s , Invoice # %s', $invoice->getOrder()->getRealOrderId(), $invoice->getIncrementId());
            $now = Mage::getSingleton('core/date')->gmtDate();
            $changeModel = Mage::getSingleton('unl_inventory/change');

            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_CANCELED) {
                $note .= ' [' . Mage::helper('unl_inventory')->__('CANCELED') . ']';
            }

            foreach ($auditLogs as $auditLog) {
                if ($auditLog->getInvoiceItem()) {
                    $auditLog->setInvoiceItemId($auditLog->getInvoiceItem()->getId());
                }

                $auditLog
                    ->setNote($note)
                    ->setCreatedAt($now);

                $changeModel->adjustInventory($auditLog);
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
            $return = false;
            if ($item->hasBackToStock()) {
                if ($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif (Mage::helper('cataloginventory')->isAutoReturnEnabled()) {
                $return = true;
            }

            if ($item->getQty() <= 0 || !$return) {
                continue;
            }

            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if (!Mage::helper('unl_inventory')->getIsAuditInventory($product)) {
                continue;
            }

            $parentOrderId = $item->getOrderItem()->getParentItemId();
            /* @var $parentItem Mage_Sales_Model_Order_Creditmemo_Item */
            $parentItem = $parentOrderId ? $creditmemo->getItemByOrderId($parentOrderId) : false;

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData(array(
                'product_id' => $item->getProductId(),
                'type' => Unl_Inventory_Model_Audit::TYPE_CREDIT,
                'qty' => $parentItem ? ($parentItem->getQty() * $item->getQty()) : $item->getQty(),
                'amount' => $item->getBaseCost() * $item->getQty(),
                'creditmemo_item' => $item,
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
            $creditmemo->unsAuditLogs();
            $note = Mage::helper('unl_inventory')->__('Order # %s , Creditmemo # %s', $creditmemo->getOrder()->getRealOrderId(), $creditmemo->getIncrementId());
            $now = Mage::getSingleton('core/date')->gmtDate();
            $changeModel = Mage::getSingleton('unl_inventory/change');

            foreach ($auditLogs as $auditLog) {
                if ($auditLog->getCreditmemoItem()) {
                    $auditLog->setCreditmemoItemId($auditLog->getCreditmemoItem()->getId());
                }

                $auditLog
                    ->setNote($note)
                    ->setCreatedAt($now);

                $changeModel->adjustInventory($auditLog);
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
        Mage::getSingleton('unl_inventory/change')
            ->handleConfigChange($this->_configBeforeSave['manage_stock'], $this->_configBeforeSave['accounting']);

        Mage::getSingleton('cataloginventory/observer')
            ->updateItemsStockUponConfigChange($observer);

        return $this;
    }
}
