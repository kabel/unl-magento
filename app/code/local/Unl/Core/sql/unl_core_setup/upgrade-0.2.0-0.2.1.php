<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addIndex(
    $installer->getTable('sales/invoice_item'),
    $installer->getIdxName('sales/invoice_item', array('order_item_id')),
    array('order_item_id')
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/creditmemo_item'),
    $installer->getIdxName('sales/creditmemo_item', array('order_item_id')),
    array('order_item_id')
);

$installer->endSetup();
