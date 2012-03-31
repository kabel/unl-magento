<?php

/* @var $installer Unl_AdminLog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create unl_adminlog/log table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_adminlog/log'))
    ->addColumn('log_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
            'identity'  => true,
            'nullable'  => false,
            'unsigned'  => true,
            'primary'   => true,
        ),
        'Log Id'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
        ),
        'Creation Date'
    )
    ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(),
        'Remote Address'
    )
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'nullable'  => false,
            'unsigned'  => true,
        ),
        'User Id'
    )
    ->addColumn('event_module', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Creation Date'
    )
    ->addColumn('action', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'unsigned'  => true,
            'default'   => '0',
        ),
        'Action Type'
    )
    ->addColumn('result', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'default'   => '0',
        ),
        'Result'
    )
    ->addColumn('action_path', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Action Path'
    )
    ->addColumn('action_info', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(),
        'Action Information'
    )
    ->addColumn('is_archived', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'nullable'  => false,
            'unsigned'  => true,
            'default'   => '0',
        ),
        'Is Archived'
    )
    ->addIndex($installer->getIdxName('unl_adminlog/log', array('is_archived')), array('is_archived'))
    ->setComment('Administrtor Action Log');
$installer->getConnection()->createTable($table);

$installer->endSetup();
