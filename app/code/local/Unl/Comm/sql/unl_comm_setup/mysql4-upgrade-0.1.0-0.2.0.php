<?php

/* @var $installer Unl_Comm_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_comm/queue_link'),
    'FK_COMM_QUEUE_LINK_QUEUE'
);

$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_comm/queue_link'),
    'FK_COMM_QUEUE_LINK_CUSTOMER'
);

/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_comm/queue_link'),
    'IDX_COMM_QUEUE_LINK_SENT_AT'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_comm/queue') => array(
        'columns' => array(
            'queue_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Queue Id'
            ),
            'queue_status' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Queue Status'
            ),
            'queue_start_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Start Date'
            ),
            'queue_finish_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Finished Date'
            ),
            'message_type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Message Type'
            ),
            'message_text' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Message Text'
            ),
            'message_styles' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'comment'   => 'Message Styles'
            ),
            'message_subject' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 300,
                'comment'   => 'Message Subject'
            ),
            'message_sender_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 200,
                'comment'   => 'Message Sender Name'
            ),
            'message_sender_email' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 200,
                'comment'   => 'Message Sender Email'
            )
        ),
        'comment' => 'Communication Queue'
    ),
    $installer->getTable('unl_comm/queue_link') => array(
        'columns' => array(
            'queue_link_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Queue Link Id'
            ),
            'queue_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Queue Id'
            ),
            'customer_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Customer Id'
            ),
            'sent_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Sent At'
            )
        ),
        'comment' => 'Communication Queue Recipient'
    )
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_comm/queue_link'),
    $installer->getIdxName('unl_comm/queue_link', array('sent_at')),
    array('sent_at')
);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_comm/queue_link', 'queue_id', 'unl_comm/queue', 'queue_id'),
    $installer->getTable('unl_comm/queue_link'),
    'queue_id',
    $installer->getTable('unl_comm/queue'),
    'queue_id'
);

$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_comm/queue_link', 'customer_id', 'customer/entity', 'entity_id'),
    $installer->getTable('unl_comm/queue_link'),
    'customer_id',
    $installer->getTable('customer/entity'),
    'entity_id'
);

$installer->endSetup();
