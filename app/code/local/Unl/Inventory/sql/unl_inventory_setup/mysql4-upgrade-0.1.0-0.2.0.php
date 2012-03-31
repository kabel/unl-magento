<?php

/* @var $installer Unl_Inventory_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_inventory/audit'),
    'FK_UNL_INVENTORY_AUDIT_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_inventory/index'),
    'FK_UNL_INVENTORY_INDEX_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_inventory/index_idx'),
    'FK_UNL_INVENTORY_INDEX_IDX_INDEX'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_inventory/audit') => array(
        'columns' => array(
            'audit_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Audit Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Product Id'
            ),
            'type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Audit Entry Type'
            ),
            'qty' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Qty'
            ),
            'amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Amount'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'nullable'  => false,
                'default'   => '0000-00-00',
                'comment'   => 'Created At'
            ),
            'note' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'Note'
            )
        ),
        'comment' => 'Inventory Audit Trail'
    ),
    $installer->getTable('unl_inventory/index') => array(
        'columns' => array(
            'index_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'identity'  => true,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Audit Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Product Id'
            ),
            'qty_on_hand' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Qty on Hand'
            ),
            'amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Amount'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'nullable'  => false,
                'default'   => '0000-00-00',
                'comment'   => 'Created At'
            )
        ),
        'comment' => 'Inventory Index'
    ),
    $installer->getTable('unl_inventory/index_idx') => array(
        'columns' => array(
            'index_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Index Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Product Id'
            )
        ),
        'comment' => 'Inventory Index Link (Index)'
    ),
    $installer->getTable('unl_inventory/index_tmp') => array(
        'columns' => array(
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'comment'   => 'Product Id'
            ),
            'qty_on_hand' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Qty on Hand'
            ),
            'amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'default'   => '0.0000',
                'comment'   => 'Amount'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'nullable'  => false,
                'default'   => '0000-00-00',
                'comment'   => 'Created At'
            )
        ),
        'engine' => 'MEMORY',
        'comment' => 'Temporary (Memory) Inventory Index Creatation Storage'
    ),
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/audit', 'product_id', 'catalog/product', 'entity_id'),
    $installer->getTable('unl_inventory/audit'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/index', 'product_id', 'catalog/product', 'entity_id'),
    $installer->getTable('unl_inventory/index'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_inventory/index_idx', 'index_id', 'unl_inventory/index', 'index_id'),
    $installer->getTable('unl_inventory/index_idx'),
    'index_id',
    $installer->getTable('unl_inventory/index'),
    'index_id'
);

$installer->endSetup();