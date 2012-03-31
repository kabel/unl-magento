<?php

/* @var $installer Unl_Payment_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_payment/account'))
    ->addColumn('account_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'identity'  => true,
            'primary'   => true,
            'nullable'  => false,
        ),
        'Payment Account Id'
    )
    ->addColumn('group_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Store Group Id'
    )
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
            'default'   => '',
        ),
        'Payment Account Name'
    )
    ->addForeignKey(
        $installer->getConnection()->getForeignKeyName($installer->getTable('unl_payment/account'), 'group_id', $installer->getTable('core/store_group'), 'group_id'),
        'group_id',
        $installer->getTable('core/store_group'),
        'group_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Payment Accounts');
$installer->getConnection()->createTable($table);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/quote_item'), 'unl_payment_account', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'UNL Payment Account'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'), 'unl_payment_account', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'UNL Payment Account'
    )
);

/**
 * Add unl_payment_account attribute to the 'eav/attribute' table
 */
$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'unl_payment_account', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Payment Account',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'unl_payment/account_source',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => true,
    'required'                   => false,
    'user_defined'               => false,
    'default'                    => '',
    'searchable'                 => false,
    'filterable'                 => false,
    'comparable'                 => false,
    'is_configurable'            => false,
    'visible_on_front'           => false,
    'visible_in_advanced_search' => false,
    'used_in_product_listing'    => false,
    'unique'                     => false,
));

$installer->endSetup();
