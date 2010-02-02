<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->addAttribute('catalog_product', 'featured_from', array(
    'type'              => 'datetime',
    'backend'           => 'eav/entity_attribute_backend_datetime',
    'frontend'          => '',
    'label'             => 'Feature From',
    'input'             => 'date',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'visible_in_advanced_search' => false,
    'unique'            => false
));

$installer->addAttribute('catalog_product', 'featured_to', array(
    'type'              => 'datetime',
    'backend'           => 'eav/entity_attribute_backend_datetime',
    'frontend'          => '',
    'label'             => 'Feature To',
    'input'             => 'date',
    'class'             => '',
    'source'            => '',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => '',
    'searchable'        => false,
    'filterable'        => false,
    'comparable'        => false,
    'visible_on_front'  => false,
    'visible_in_advanced_search' => false,
    'unique'            => false
));

$installer->endSetup();
