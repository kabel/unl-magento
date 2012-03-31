<?php

/* @var $installer Unl_Inventory_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/audit'))
    ->addColumn('audit_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Audit Id'
    )
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Product Id'
    )
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Audit Entry Type'
    )
    ->addColumn('qty', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Qty'
    )
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Amount'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Created At'
    )
    ->addColumn('note', Varien_Db_Ddl_Table::TYPE_TEXT, '1M', array(
            'nullable'  => false,
            'default'   => '',
        ),
        'Note'
    )
    ->addForeignKey(
        $installer->getFkName('unl_inventory/audit', 'product_id', 'catalog/product', 'entity_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Inventory Audit Trail');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/index'))
    ->addColumn('index_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Audit Id'
    )
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Product Id'
    )
    ->addColumn('qty_on_hand', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Qty on Hand'
    )
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Amount'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Created At'
    )
    ->addForeignKey(
        $installer->getFkName('unl_inventory/index', 'product_id', 'catalog/product', 'entity_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Inventory Index');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/index_idx'))
    ->addColumn('index_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Index Id'
    )
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Product Id'
    )
    ->addForeignKey(
        $installer->getFkName('unl_inventory/index_idx', 'index_id', 'unl_inventory/index', 'index_id'),
        'index_id',
        $installer->getTable('unl_inventory/index'),
        'index_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Inventory Index Link (Index)');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_inventory/index_tmp'))
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
        ),
        'Product Id'
    )
    ->addColumn('qty_on_hand', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Qty on Hand'
    )
    ->addColumn('amount', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
            'default'   => '0.0000',
        ),
        'Amount'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Created At'
    )
    ->setOption('type', 'MEMORY')
    ->setComment('Temporary (Memory) Inventory Index Creatation Storage');
$installer->getConnection()->createTable($table);

/**
 * Add audit_inventory attribute to the 'eav/attribute' table
 */
$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'audit_inventory', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Audit Inventory',
    'input'                      => '',
    'class'                      => '',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => false,
    'required'                   => false,
    'user_defined'               => false,
    'default'                    => false,
    'searchable'                 => false,
    'filterable'                 => false,
    'comparable'                 => false,
    'is_configurable'            => false,
    'visible_on_front'           => false,
    'visible_in_advanced_search' => false,
    'used_in_product_listing'    => false,
    'unique'                     => false,
    'apply_to'                   => 'simple,virtual,downloadable'
));

$installer->endSetup();
