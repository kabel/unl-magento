<?php

/* @var $installer Unl_Ship_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_ship/shipment_package'),
    $installer->getIdxName('unl_ship/shipment_package', array('package_id', 'order_id')),
    array('package_id', 'order_id')
);

$installer->endSetup();
