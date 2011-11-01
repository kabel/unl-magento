<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('customer_entity'), 'previous_group_id', 'smallint(3) unsigned');
$installer->addAttribute('customer', 'previous_group_id', array(
    'type'          => 'static',
    'input'         => 'select',
    'label'         => 'Previous Group',
    'source'        => 'customer/customer_attribute_source_group',
    'sort_order'    => 71,
));

$installer->endSetup();
