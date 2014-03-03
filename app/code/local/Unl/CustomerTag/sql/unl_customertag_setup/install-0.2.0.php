<?php

/* @var $installer Unl_CustomerTag_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_customertag/tag'))
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Tag Id'
    )
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
            'nullable'  => false,
            'default'   => '',
        ),
        'Tag Name'
    )
    ->addColumn('is_system', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'System Tag Flag'
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_customertag/tag'), array('name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    )
    ->setComment('Customer Tag');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_customertag/link'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Link Id'
    )
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Tag Id'
    )
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Customer Id'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Created At'
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
        'tag_id',
        $installer->getTable('unl_customertag/tag'),
        'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/link', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id',
        $installer->getTable('customer/entity'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Customer Tag Link');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_customertag/product_link'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Link Id'
    )
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Tag Id'
    )
    ->addColumn('product_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Product Id'
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/product_link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
        'tag_id',
        $installer->getTable('unl_customertag/tag'),
        'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/product_link', 'product_id', 'catalog/product', 'entity_id'),
        'product_id',
        $installer->getTable('catalog/product'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Customer Tag Product Link');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_customertag/category_link'))
    ->addColumn('link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Link Id'
    )
    ->addColumn('tag_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Tag Id'
    )
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Category Id'
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/category_link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
        'tag_id',
        $installer->getTable('unl_customertag/tag'),
        'tag_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey(
        $installer->getFkName('unl_customertag/category_link', 'category_id', 'catalog/category', 'entity_id'),
        'category_id',
        $installer->getTable('catalog/category'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Customer Tag Category Link');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/quote'), 'customer_tag_ids', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '1M',
        'nullable'  => false,
        'default'   => '',
        'comment'   => 'Customer Tag Ids'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'), 'customer_tag_ids', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '1M',
        'nullable'  => false,
        'default'   => '',
        'comment'   => 'Customer Tag Ids'
    )
);

$installer->endSetup();
