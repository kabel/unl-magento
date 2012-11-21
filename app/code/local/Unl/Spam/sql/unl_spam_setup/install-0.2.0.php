<?php

/* @var $installer Unl_Spam_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_spam/sfs_cache'))
    ->addColumn('cache_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Cache Id'
    )
    ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_VARBINARY, 16, array(
            'nullable'  => false,
        ),
        'Remote IP Address'
    )
    ->addColumn('expires_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Cache Expiration Time'
    )
    ->addColumn('appears', Varien_Db_Ddl_Table::TYPE_TINYINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
        ),
        'Appears in SFS list'
    )
    ->addColumn('confidence', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(),
        'SFS Spam Confidence'
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_spam/sfs_cache'), array('remote_addr'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('remote_addr'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('SFS IP Lookup Cache');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_spam/quarantine'))
    ->addColumn('quarantine_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Quarantine Id'
    )
    ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_VARBINARY, 16, array(
            'nullable'  => false,
        ),
        'Remote IP Address'
    )
    ->addColumn('expires_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Quarantine Expiration Time'
    )
    ->addColumn('strikes', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '1',
        ),
        'Hits on the quarantine'
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_spam/quarantine'), array('remote_addr'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('remote_addr'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('SPAM IP Quarantine');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_spam/blacklist'))
    ->addColumn('blacklist_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Cache Id'
    )
    ->addColumn('remote_addr', Varien_Db_Ddl_Table::TYPE_VARBINARY, 16, array(
        'nullable'  => false,
    ),
        'Remote IP Address'
    )
    ->addColumn('cidr_mask', Varien_Db_Ddl_Table::TYPE_VARBINARY, 16, array(),
        'IP CIDR Address Mask'
    )
    ->addColumn('response_type', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Response type'
    )
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
        'default'   => '0000-00-00',
    ),
        'Blacklisted datetime'
    )
    ->addColumn('last_seen', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
        'nullable'  => false,
        'default'   => '0000-00-00',
    ),
        'Blacklisted datetime'
    )
    ->addColumn('strikes', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
    ),
        'Hits on the blacklist'
    )
    ->addColumn('comment', Varien_Db_Ddl_Table::TYPE_TEXT, 1024, array(),
        'Blacklist reasoning'
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_spam/blacklist'), array('remote_addr', 'cidr_mask'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('remote_addr', 'cidr_mask'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->addIndex($installer->getIdxName($installer->getTable('unl_spam/blacklist'), array('last_seen'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('last_seen'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX)
    )
    ->setComment('SPAM IP Blacklist');
$installer->getConnection()->createTable($table);

$installer->endSetup();
