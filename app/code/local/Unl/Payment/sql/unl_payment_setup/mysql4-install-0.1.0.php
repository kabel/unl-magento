<?php

/* @var $this Mage_Core_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS `{$installer->getTable('unl_payment/account')}`;
CREATE TABLE `{$installer->getTable('unl_payment/account')}` (
    `account_id` int(10) unsigned NOT NULL auto_increment,
    `group_id` smallint(5) unsigned NOT NULL,
    `name` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`account_id`),
    KEY `FK_UNL_PAYMENT_ACCOUNT_GROUP` (`group_id`),
    CONSTRAINT `FK_UNL_PAYMENT_ACCOUNT_GROUP` FOREIGN KEY (`group_id`) REFERENCES `{$installer->getTable('core/store_group')}` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->getConnection()->addColumn($installer->getTable('sales/quote_item'), 'unl_payment_account', 'int unsigned default NULL AFTER `source_store_view`');
$installer->getConnection()->addColumn($installer->getTable('sales/order_item'), 'unl_payment_account', 'int unsigned default NULL AFTER `source_store_view`');

/**
 * Add unl_payment_account attribute to the 'eav/attribute' table
 */
$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'unl_payment_account', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Payment Account',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'unl_payment/account_source',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => true,
    'required'                   => false,
    'user_defined'               => false,
    'default'                    => '',
    'searchable'                 => false,
    'filterable'                 => false,
    'comparable'                 => false,
    'visible_on_front'           => false,
    'visible_in_advanced_search' => false,
    'unique'                     => false
));

$installer->endSetup();
