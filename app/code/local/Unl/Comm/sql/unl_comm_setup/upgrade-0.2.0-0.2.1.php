<?php

/* @var $installer Unl_Comm_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_comm/queue'),
    $installer->getIdxName('unl_comm/queue', array('queue_status', 'queue_start_at')),
    array('queue_status', 'queue_start_at')
);

$installer->endSetup();
