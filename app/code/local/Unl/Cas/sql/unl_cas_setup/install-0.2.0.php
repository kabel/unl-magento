<?php

/* @var $installer Unl_Cas_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Add unl_cas_uid attribute to the 'eav/attribute' table
 */
/* @var $customerInstaller Mage_Catalog_Model_Resource_Setup */
$customerInstaller = Mage::getResourceModel('customer/setup', 'customer_setup');
$customerInstaller->addAttribute('customer', 'unl_cas_uid', array(
    'type'         => 'varchar',
    'label'        => 'UNL CAS UID',
    'input'        => 'text',
    'required'     => false,
    'sort_order'   => 85,
    'visible'      => false,
    'system'       => true,
    'adminhtml_only' => true,
));

$installer->getConnection()->addColumn(
    $installer->getTable('customer/entity'),
    'previous_group_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Previous Group'
    )
);
$customerInstaller->addAttribute('customer', 'previous_group_id', array(
    'type'         => 'static',
    'label'        => 'Previous Group',
    'input'        => 'select',
    'source'       => 'customer/customer_attribute_source_group',
    'required'     => false,
    'sort_order'   => 71,
    'visible'      => false,
    'system'       => true,
));

$installer->endSetup();
