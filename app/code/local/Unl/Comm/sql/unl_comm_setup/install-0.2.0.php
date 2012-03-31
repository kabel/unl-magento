<?php

/* @var $installer Unl_Comm_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Create unl_comm/queue table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_comm/queue'))
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Queue Id'
    )
    ->addColumn('queue_status', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Queue Status'
    )
    ->addColumn('queue_start_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(),
        'Start Date'
    )
    ->addColumn('queue_finish_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(),
        'Finished Date'
    )
    ->addColumn('message_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
        ),
        'Message Type'
    )
    ->addColumn('message_text', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(),
        'Message Text'
    )
    ->addColumn('message_styles', Varien_Db_Ddl_Table::TYPE_TEXT, '1M', array(),
        'Message Styles'
    )
    ->addColumn('message_subject', Varien_Db_Ddl_Table::TYPE_TEXT, 300, array(),
        'Message Subject'
    )
    ->addColumn('message_sender_name', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(),
        'Message Sender Name'
    )
    ->addColumn('message_sender_email', Varien_Db_Ddl_Table::TYPE_TEXT, 200, array(),
        'Message Sender Email'
    )
    ->setComment('Communication Queue');
$installer->getConnection()->createTable($table);

/**
 * Create unl_comm/queue_link table
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_comm/queue_link'))
    ->addColumn('queue_link_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Queue Link Id'
    )
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
         ),
        'Queue Id'
    )
    ->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Customer Id'
    )
    ->addColumn('sent_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(),
        'Sent At'
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_comm/queue_link'), array('sent_at')), array('sent_at'))
    ->addForeignKey($installer->getFkName('unl_comm/queue_link', 'queue_id', 'unl_comm/queue', 'queue_id'),
        'queue_id', $installer->getTable('unl_comm/queue'), 'queue_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->addForeignKey($installer->getFkName('unl_comm/queue_link', 'customer_id', 'customer/entity', 'entity_id'),
        'customer_id', $installer->getTable('customer/entity'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE
    )
    ->setComment('Communication Queue Recipient');
$installer->getConnection()->createTable($table);

$installer->endSetup();
