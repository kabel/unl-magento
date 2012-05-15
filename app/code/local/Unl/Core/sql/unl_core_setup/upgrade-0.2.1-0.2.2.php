<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('begin_date')),
    array('begin_date')
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('end_date')),
    array('end_date')
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_code_low', 'zip_code_high')),
    array('zip_code_low', 'zip_code_high')
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_ext_low', 'zip_ext_high')),
    array('zip_ext_low', 'zip_ext_high')
);

$installer->endSetup();
