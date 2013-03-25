<?php

/* @var $installer Unl_Inventory_Model_Resource_Setup */
$installer = $this;

$installer->getConnection()->update($installer->getTable('unl_inventory/purchase'), array(
    'qty' => new Zend_Db_Expr($installer->getConnection()->quoteIdentifier('qty_on_hand')),
    'amount' => new Zend_Db_Expr($installer->getConnection()->quoteIdentifier('amount_remaining'))
));

$installer->getConnection()->delete($installer->getTable('unl_inventory/audit'), array(
    'qty = ?' => 0,
    'amount = ?' => 0,
    'type IN (?)' => array(
        Unl_Inventory_Model_Audit::TYPE_SALE,
        Unl_Inventory_Model_Audit::TYPE_CREDIT,
    ),
));
