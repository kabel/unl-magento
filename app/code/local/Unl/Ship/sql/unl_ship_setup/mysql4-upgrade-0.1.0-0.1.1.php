<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$setup->addAttribute('catalog_product', 'ships_separately', array(
    'type'              => 'int',
    'label'             => 'Ships Separately',
    'input'             => 'select',
    'source'            => 'eav/entity_attribute_source_boolean',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'unique'            => false,
    'apply_to'          => 'simple,configurable',
    'group'             => 'Shipping',
    'sort_order'        => 10
));

$installer->endSetup();
