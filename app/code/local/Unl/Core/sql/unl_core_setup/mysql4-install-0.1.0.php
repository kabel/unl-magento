<?php

$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'source_store_view', array(
	'type'              => 'int',
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Source Store',
	'input'             => 'select',
	'class'             => '',
	'source'            => 'unl_core/store_source_switcher',
	'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
	'visible'           => true,
	'required'          => true,
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
