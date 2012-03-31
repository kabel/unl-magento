<?php

/* @var $installer Unl_Notify_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_notify/order_queue') => array(
        'columns' => array(
            'queue_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'primary'   => true,
                'identity'  => true,
                'comment'   => 'Queue Id'
            ),
            'order_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Order Id'
            )
        ),
        'comment' => 'Orders queued to send notification'
    )
);
$installer->getConnection()->modifyTables($tables);

$installer->endSetup();
