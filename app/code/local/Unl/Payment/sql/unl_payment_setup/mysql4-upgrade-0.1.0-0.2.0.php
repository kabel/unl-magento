<?php

/* @var $installer Unl_Payment_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_payment/account'),
    'FK_UNL_PAYMENT_ACCOUNT_GROUP'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_payment/account') => array(
        'columns' => array(
            'account_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'identity'  => true,
                'primary'   => true,
                'nullable'  => false,
                'comment'   => 'Payment Account Id'
            ),
            'group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Store Group Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'Payment Account Name'
            )
        ),
        'comment' => 'Payment Accounts'
    )
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getConnection()->getForeignKeyName($installer->getTable('unl_payment/account'), 'group_id', $installer->getTable('core/store_group'), 'group_id'),
    $installer->getTable('unl_payment/account'),
    'group_id',
    $installer->getTable('core/store_group'),
    'group_id'
);

$installer->endSetup();
