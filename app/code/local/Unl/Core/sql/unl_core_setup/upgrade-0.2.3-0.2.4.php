<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop bad indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('record_type'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('city_name', 'street_name'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_code', 'street_name'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('begin_date'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('end_date'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_code_low', 'zip_code_high'))
);

$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_ext_low', 'zip_ext_high'))
);


/**
 * Add new indexes
 */

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('city_name')),
    array('city_name')
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_code')),
    array('zip_code')
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array(
        'street_name',
        'low_address_range',
        'high_address_range',
    )),
    array(
        'street_name',
        'low_address_range',
        'high_address_range',
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array(
        'record_type',
        'zip_code_low',
        'zip_code_high',
    )),
    array(
        'record_type',
        'zip_code_low',
        'zip_code_high',
    )
);

$installer->endSetup();
