<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('unl_core/warehouse')};
CREATE TABLE {$this->getTable('unl_core/warehouse')} (
  warehouse_id int(10) unsigned NOT NULL auto_increment,
  name varchar(255) NOT NULL default '',
  email varchar(255) NOT NULL default '',
  PRIMARY KEY(`warehouse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$setup->addAttribute('catalog_product', 'warehouse', array(
    'type'              => 'int',
    'label'             => 'Warehouse',
    'input'             => 'select',
    'source'            => 'unl_core/warehouse_source',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => false,
    'unique'            => false,
    'group'             => 'Shipping',
    'apply_to'          => 'simple,bundle',
));

$installer->getConnection()->addColumn($this->getTable('admin/user'), 'warehouse_scope', 'text default NULL');

$installer->getConnection()->addColumn($this->getTable('sales/quote_item'), 'warehouse', 'int(10) unsigned default NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_item'), 'warehouse', 'int(10) unsigned default NULL');

$installer->endSetup();
