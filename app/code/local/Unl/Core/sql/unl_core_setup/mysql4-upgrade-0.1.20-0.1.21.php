<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'is_dummy', 'TINYINT(1) UNSIGNED DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_item'), 'is_dummy', 'TINYINT(1) UNSIGNED DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/invoice_item'), 'is_dummy', 'TINYINT(1) UNSIGNED DEFAULT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/shipment_item'), 'is_dummy', 'TINYINT(1) UNSIGNED DEFAULT NULL');

$orderItemcollection = Mage::getModel('sales/order_item')->getResourceCollection();
foreach ($orderItemcollection as $item) {
    $dummy = $item->isDummy();
    $item->setIsDummy($dummy);
    $item->save();
}

$collection = Mage::getModel('sales/order_creditmemo_item')->getResourceCollection();
foreach ($collection as $item) {
    $dummy = $orderItemcollection->getItemById($item->getOrderItemId())->isDummy();
    $item->setIsDummy($dummy);
    $item->save();
}

$collection = Mage::getModel('sales/order_invoice_item')->getResourceCollection();
foreach ($collection as $item) {
    $dummy = $orderItemcollection->getItemById($item->getOrderItemId())->isDummy();
    $item->setIsDummy($dummy);
    $item->save();
}

$collection = Mage::getModel('sales/order_shipment_item')->getResourceCollection();
foreach ($collection as $item) {
    $dummy = $orderItemcollection->getItemById($item->getOrderItemId())->isDummy(true);
    $item->setIsDummy($dummy);
    $item->save();
}

$installer->endSetup();
