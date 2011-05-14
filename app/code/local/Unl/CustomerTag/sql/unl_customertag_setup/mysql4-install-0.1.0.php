<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
-- DROP TABLE IF EXISTS `{$this->getTable('unl_customertag/tag')}`;
CREATE TABLE `{$this->getTable('unl_customertag/tag')}` (
    `tag_id` int(10) unsigned NOT NULL auto_increment,
    `name` varchar(255) NOT NULL default '',
    `is_system` tinyint(1) unsigned NOT NULL default '0',
    PRIMARY KEY  (`tag_id`),
    UNIQUE KEY `IX_TAG_NAME_UNIQUE` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_customertag/link')}`;
CREATE TABLE `{$this->getTable('unl_customertag/link')}` (
    `link_id` int(10) unsigned NOT NULL auto_increment,
    `tag_id` int(10) unsigned NOT NULL default '0',
    `customer_id` int(10) unsigned NOT NULL default '0',
    `created_at` datetime NOT NULL default '0000-00-00',
    PRIMARY KEY  (`link_id`),
    KEY `FK_UNL_CUSTOMERTAG_LINK_TAG` (`tag_id`),
    KEY `FK_UNL_CUSTOMERTAG_LINK_CUSTOMER` (`customer_id`),
    CONSTRAINT `FK_UNL_CUSTOMERTAG_LINK_TAG` FOREIGN KEY (`tag_id`) REFERENCES `{$this->getTable('unl_customertag/tag')}` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_UNL_CUSTOMERTAG_LINK_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_customertag/product_link')}`;
CREATE TABLE `{$this->getTable('unl_customertag/product_link')}` (
    `link_id` int(10) unsigned NOT NULL auto_increment,
    `tag_id` int(10) unsigned NOT NULL default '0',
    `product_id` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`link_id`),
    KEY `FK_UNL_CUSTOMERTAG_PRODUCT_LINK_TAG` (`tag_id`),
    KEY `FK_UNL_CUSTOMERTAG_PRODUCT_LINK_PRODUCT` (`product_id`),
    CONSTRAINT `FK_UNL_CUSTOMERTAG_PRODUCT_LINK_TAG` FOREIGN KEY (`tag_id`) REFERENCES `{$this->getTable('unl_customertag/tag')}` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_UNL_CUSTOMERTAG_PRODUCT_LINK_PRODUCT` FOREIGN KEY (`product_id`) REFERENCES `{$this->getTable('catalog_product_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS `{$this->getTable('unl_customertag/category_link')}`;
CREATE TABLE `{$this->getTable('unl_customertag/category_link')}` (
    `link_id` int(10) unsigned NOT NULL auto_increment,
    `tag_id` int(10) unsigned NOT NULL default '0',
    `category_id` int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`link_id`),
    KEY `FK_UNL_CUSTOMERTAG_CATEGORY_LINK_TAG` (`tag_id`),
    KEY `FK_UNL_CUSTOMERTAG_CATEGORY_LINK_CATEGORY` (`category_id`),
    CONSTRAINT `FK_UNL_CUSTOMERTAG_CATEGORY_LINK_TAG` FOREIGN KEY (`tag_id`) REFERENCES `{$this->getTable('unl_customertag/tag')}` (`tag_id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_UNL_CUSTOMERTAG_CATEGORY_LINK_CATEGORY` FOREIGN KEY (`category_id`) REFERENCES `{$this->getTable('catalog_category_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{$installer->getTable('unl_customertag/tag')}`
    (`name`, `is_system`) VALUES
    ('Allow Invoicing', 1)
;
");

$installer->endSetup();
