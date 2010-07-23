<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$setup->addAttribute('catalog_product', 'limit_sale_qty', array(
    'type'              => 'decimal',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Maximum Qty Allowed to Purchase',
    'input'             => 'text',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => false,
    'unique'            => false,
    'is_configurable'   => false,
    'group'             => 'Security'
));

$installer->endSetup();