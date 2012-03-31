<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'ships_separately', array(
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
