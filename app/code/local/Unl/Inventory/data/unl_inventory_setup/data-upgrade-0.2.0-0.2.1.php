<?php

/* @var $installer Unl_Inventory_Model_Resource_Setup */
$installer = $this;

$installer->getConnection()->update($installer->getTable('unl_inventory/purchase'), array(
    'qty' => new Zend_Db_Expr($installer->getConnection()->quoteIdentifier('qty_on_hand')),
    'amount' => new Zend_Db_Expr($installer->getConnection()->quoteIdentifier('amount_remaining'))
));
