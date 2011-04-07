<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('unl_inventory/audit')}`;
CREATE TABLE `{$this->getTable('unl_inventory/audit')}` (
    `audit_id` int(10) unsigned NOT NULL auto_increment,
    `product_id` int(10) unsigned NOT NULL default '0',
    `type` tinyint(1) unsigned NOT NULL default '0',
    `qty` decimal(12,4) NOT NULL default '0.0000',
    `amount` decimal(12,4) NOT NULL default '0.0000',
    `created_at` DATETIME NOT NULL default '0000-00-00',
    `note` text NOT NULL default '',
    PRIMARY KEY  (`audit_id`),
    KEY `FK_UNL_INVENTORY_AUDIT_PRODUCT` (`product_id`),
    CONSTRAINT `FK_UNL_INVENTORY_AUDIT_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_inventory/index')}`;
CREATE TABLE `{$this->getTable('unl_inventory/index')}` (
    `index_id` int(10) unsigned NOT NULL auto_increment,
    `product_id` int(10) unsigned NOT NULL default '0',
    `qty_on_hand` decimal(12,4) NOT NULL default '0.0000',
    `amount` decimal(12,4) NOT NULL default '0.0000',
    `created_at` DATETIME NOT NULL default '0000-00-00',
    PRIMARY KEY  (`index_id`),
    KEY `FK_UNL_INVENTORY_INDEX_PRODUCT` (`product_id`),
    CONSTRAINT `FK_UNL_INVENTORY_INDEX_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_inventory/index_idx')}`;
CREATE TABLE `{$this->getTable('unl_inventory/index_idx')}` (
    `index_id` int(10) unsigned NOT NULL,
    `product_id` int(10) unsigned NOT NULL,
    PRIMARY KEY  (`product_id`),
    KEY `FK_UNL_INVENTORY_INDEX_IDX_INDEX` (`index_id`),
    CONSTRAINT `FK_UNL_INVENTORY_INDEX_IDX_INDEX` FOREIGN KEY (`index_id`) REFERENCES `{$this->getTable('unl_inventory/index')}` (`index_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_inventory/index_tmp')}`;
CREATE TABLE `{$installer->getTable('unl_inventory/index_tmp')}` (
     `product_id` int(10) unsigned NOT NULL,
     `qty_on_hand` decimal(12,4) NOT NULL default '0.0000',
     `amount` decimal(12,4) NOT NULL default '0.0000',
     `created_at` DATETIME NOT NULL default '0000-00-00',
     PRIMARY KEY  (`product_id`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;
");

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$setup->addAttribute('catalog_product', 'audit_inventory', array(
    'type'                 => 'int',
    'label'                => 'Audit Inventory',
    'input'                => '',
    'global'               => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'              => false,
    'required'             => false,
    'user_defined'         => false,
    'default'              => false,
    'unique'               => false,
    'used_for_price_rules' => false,
    'is_configurable'      => false,
    'apply_to'             => 'simple,virtual',
));

$installer->endSetup();
