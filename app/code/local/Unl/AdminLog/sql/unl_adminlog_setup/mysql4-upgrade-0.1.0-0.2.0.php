<?php

/* @var $installer Unl_AdminLog_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_adminlog/log'),
    'IDX_UNL_ADMINLOG_ARCHIVED'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_adminlog/log') => array(
        'columns' => array(
            'log_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'identity'  => true,
                'nullable'  => false,
                'unsigned'  => true,
                'primary'   => true,
                'comment'   => 'Log Id'
            ),
            'created_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'nullable'  => false,
                'comment'   => 'Creation Date'
            ),
            'remote_addr' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_BIGINT,
                'comment'   => 'Remote Address'
            ),
            'user_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'nullable'  => false,
                'unsigned'  => true,
                'comment'   => 'User Id'
            ),
            'event_module' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Creation Date'
            ),
            'action' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'unsigned'  => true,
                'default'   => '0',
                'comment'   => 'Action Type'
            ),
            'result' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Result'
            ),
            'action_path' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Action Path'
            ),
            'action_info' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '2M',
                'comment'   => 'Action Information'
            ),
            'is_archived' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'nullable'  => false,
                'unsigned'  => true,
                'default'   => '0',
                'comment'   => 'Is Archived'
            )
        ),
        'comment' => 'Administrtor Action Log'
    )
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_adminlog/log'),
    $installer->getIdxName('unl_adminlog/log', array('is_archived')),
    array('is_archived')
);

$installer->endSetup();
