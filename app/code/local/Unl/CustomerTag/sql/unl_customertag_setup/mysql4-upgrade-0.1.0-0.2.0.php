<?php

/* @var $installer Unl_CustomerTag_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/link'),
    'FK_UNL_CUSTOMERTAG_LINK_TAG'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/link'),
    'FK_UNL_CUSTOMERTAG_LINK_CUSTOMER'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/product_link'),
    'FK_UNL_CUSTOMERTAG_PRODUCT_LINK_TAG'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/product_link'),
    'FK_UNL_CUSTOMERTAG_PRODUCT_LINK_PRODUCT'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/category_link'),
    'FK_UNL_CUSTOMERTAG_CATEGORY_LINK_TAG'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_customertag/category_link'),
    'FK_UNL_CUSTOMERTAG_CATEGORY_LINK_CATEGORY'
);

/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_customertag/tag'),
    'IX_TAG_NAME_UNIQUE'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_customertag/tag') => array(
        'columns' => array(
            'tag_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Tag Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'Tag Name'
            ),
            'is_system' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'System Tag Flag'
            )
        ),
        'comment' => 'Customer Tag'
    ),
    $installer->getTable('unl_customertag/link') => array(
        'columns' => array(
            'link_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Link Id'
            ),
            'tag_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Tag Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'length'    => 255,
                'nullable'  => false,
                'default'   => '0000-00-00',
                'comment'   => 'Created At'
            )
        ),
        'comment' => 'Customer Tag Link'
    ),
    $installer->getTable('unl_customertag/product_link') => array(
        'columns' => array(
            'link_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Link Id'
            ),
            'tag_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Tag Id'
            ),
            'product_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Product Id'
            )
        ),
        'comment' => 'Customer Tag Product Link'
    ),
    $installer->getTable('unl_customertag/category_link') => array(
        'columns' => array(
            'link_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Link Id'
            ),
            'tag_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Tag Id'
            ),
            'category_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Category Id'
            )
        ),
        'comment' => 'Customer Tag Category Link'
    ),

    $installer->getTable('sales/quote') => array(
        'columns' => array(
            'customer_tag_ids' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'Customer Tag Ids'
            )
        )
    ),
    $installer->getTable('sales/order') => array(
        'columns' => array(
            'customer_tag_ids' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'Customer Tag Ids'
            )
        )
    )
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_customertag/tag'),
    $installer->getIdxName('unl_customertag/tag', array('name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
    array('name'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
    $installer->getTable('unl_customertag/link'),
    'tag_id',
    $installer->getTable('unl_customertag/tag'),
    'tag_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/link', 'customer_id', 'customer/entity', 'entity_id'),
    $installer->getTable('unl_customertag/link'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/product_link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
    $installer->getTable('unl_customertag/product_link'),
    'tag_id',
    $installer->getTable('unl_customertag/tag'),
    'tag_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/product_link', 'product_id', 'catalog/product', 'entity_id'),
    $installer->getTable('unl_customertag/product_link'),
    'product_id',
    $installer->getTable('catalog/product'),
    'entity_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/category_link', 'tag_id', 'unl_customertag/tag', 'tag_id'),
    $installer->getTable('unl_customertag/category_link'),
    'tag_id',
    $installer->getTable('unl_customertag/tag'),
    'tag_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_customertag/category_link', 'category_id', 'catalog/category', 'entity_id'),
    $installer->getTable('unl_customertag/category_link'),
    'category_id',
    $installer->getTable('catalog/category'),
    'entity_id'
);

$installer->endSetup();
