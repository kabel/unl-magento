<?php

/* @var $installer Unl_Spam_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->modifyColumn(
    $installer->getTable('unl_spam/blacklist'),
    'last_seen',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'default' => null,
        'comment' => 'Last datetime of hit'
    )
);

$installer->endSetup();
