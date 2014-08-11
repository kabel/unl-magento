<?php

/* @var $installer Unl_Inventory_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop unneeded tables
 */
$installer->getConnection()->dropTable($installer->getTable('unl_inventory_index_idx'));

$installer->getConnection()->dropColumn($installer->getTable('unl_inventory/index_tmp'), 'created_at');

/**
 * Rename table
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_inventory_index'),
    $installer->getFkName('unl_inventory_index', 'product_id', 'catalog/product', 'entity_id')
);
$installer->getConnection()->renameTable($installer->getTable('unl_inventory_index'), $installer->getTable('unl_inventory/purchase'));
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/purchase', 'product_id', 'catalog/product', 'entity_id'),
    $installer->getTable('unl_inventory/purchase'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);
$installer->getConnection()->addIndex(
    $installer->getTable('unl_inventory/purchase'),
    $installer->getIdxName($installer->getTable('unl_inventory/purchase'), array('product_id', 'created_at')),
    array('product_id', 'created_at')
);

/**
 * Add/Modify columns
 */
$installer->getConnection()->changeColumn($installer->getTable('unl_inventory/purchase'), 'amount', 'amount_remaining', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'scale'     => 4,
    'precision' => 12,
    'nullable'  => false,
    'default'   => '0.0000',
    'comment'   => 'Amount Remaining'
));

$installer->getConnection()->changeColumn($installer->getTable('unl_inventory/purchase'), 'index_id', 'purchase_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'identity'  => true,
    'unsigned'  => true,
    'nullable'  => false,
    'primary'   => true,
    'comment'   => 'Purchase Id'
));

$installer->getConnection()->addColumn($installer->getTable('unl_inventory/purchase'), 'qty', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'scale'     => 4,
    'precision' => 12,
    'nullable'  => false,
    'default'   => '0.0000',
    'comment'   => 'Qty'
));

$installer->getConnection()->addColumn($installer->getTable('unl_inventory/purchase'), 'amount', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
    'scale'     => 4,
    'precision' => 12,
    'nullable'  => false,
    'default'   => '0.0000',
    'comment'   => 'Amount'
));

$installer->getConnection()->addColumn($installer->getTable('unl_inventory/audit'), 'invoice_item_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Invoice Item Id'
));

$installer->getConnection()->addColumn($installer->getTable('unl_inventory/audit'), 'creditmemo_item_id', array(
    'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
    'unsigned'  => true,
    'nullable'  => true,
    'comment'   => 'Credit memo Item Id'
));

/**
 * Create new tables
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/purchase_audit'))
    ->addColumn('purchase_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('audit_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ))
    ->addColumn('qty_affected', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => true,
        'default'   => null,
    ), 'Qty affected by association')
    ->addForeignKey(
        $installer->getFkName('unl_inventory/purchase_audit', 'purchase_id', 'unl_inventory/purchase', 'purchase_id'),
        'purchase_id',
        $installer->getTable('unl_inventory/purchase'),
        'purchase_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('unl_inventory/purchase_audit', 'audit_id', 'unl_inventory/audit', 'audit_id'),
        'audit_id',
        $installer->getTable('unl_inventory/audit'),
        'audit_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Linking table purchase-to-audit');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/backorder'))
    ->addColumn('backorder_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned' => true,
        'nullable' => false,
        'primary'  => true,
    ), 'Backorder Id')
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ), 'Product Id')
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
        'nullable'  => false,
        'default'   => '0.0000',
    ), 'Qty')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
    ), 'Parent (Invoice Item) Id')
    ->addForeignKey(
        $installer->getFkName('unl_inventory/backorder', 'product_id', 'catalog/product', 'entity_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('unl_inventory/backorder', 'parent_id', 'sales/invoice_item', 'entity_id'),
        'parent_id',
        $installer->getTable('sales/invoice_item'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Backorders');
$installer->getConnection()->createTable($table);

/**
 * Create new foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/audit', 'invoice_item_id', 'sales/invoice_item', 'entity_id'),
    $installer->getTable('unl_inventory/audit'),
    'invoice_item_id',
    $installer->getTable('sales/invoice_item'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/audit', 'creditmemo_item_id', 'sales/creditmemo_item', 'entity_id'),
    $installer->getTable('unl_inventory/audit'),
    'creditmemo_item_id',
    $installer->getTable('sales/creditmemo_item'),
    'entity_id'
);

$installer->endSetup();
