<?php

/* @var $this Mage_Eav_Model_Entity_Setup */
$installer = $this;

$installer->startSetup();

$conn = $installer->getConnection();
$conn->addColumn($this->getTable('sales/quote_item'), 'source_store_view', 'int(10) unsigned default NULL AFTER `sku`');

$select = $conn->select()
    ->from(array('e' => $this->getTable('sales/quote_item')))
    ->where('e.source_store_view IS NULL');

$stmt = $conn->query($select);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $product = Mage::getModel('catalog/product');
    /* @var $product Mage_Catalog_Model_Product */
    $quote_item = Mage::getModel('sales/quote_item');
    /* @var $product Mage_Sales_Model_Quote_Item */
    $product->load($row['product_id']);
    $quote_item->load($row['item_id']);
    $quote_item->setData('product', $product);
    $quote_item->setSourceStoreView($product->getSourceStoreView());
    $quote_item->save();
}

$conn->addColumn($this->getTable('sales/order_item'), 'source_store_view', 'int(10) unsigned default NULL AFTER `sku`');

$select = $conn->select()
    ->from(array('e' => $this->getTable('sales/order_item')))
    ->where('e.source_store_view IS NULL');

$stmt = $conn->query($select);
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    $order_item = Mage::getModel('sales/order_item');
    /* @var $product Mage_Sales_Model_Order_Item */
    $quote_item = Mage::getModel('sales/quote_item');
    /* @var $product Mage_Sales_Model_Quote_Item */
    $quote_item->load($row['quote_item_id']);
    $order_item->load($row['item_id']);
    $order_item->setSourceStoreView($quote_item->getSourceStoreView());
    $order_item->save();
}

$installer->endSetup();
