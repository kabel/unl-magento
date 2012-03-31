<?php

/* @var $installer Unl_Cas_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('customer/entity') => array(
        'columns' => array (
            'previous_group_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Previous Group'
            )
        )
    )
);
$installer->getConnection()->modifyTables($tables);

$installer->endSetup();
