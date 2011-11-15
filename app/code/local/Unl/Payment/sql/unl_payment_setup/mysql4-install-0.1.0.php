<?php

/* @var $this Mage_Eav_Model_Entity_Setup */
$installer = $this;
$conn = $installer->getConnection();

$installer->startSetup();

$installer->addAttribute('catalog_product', 'unl_payment_account', array(
    'type'              => 'int',
    'backend'           => '',
    'frontend'          => '',
    'label'             => 'Payment Account',
    'input'             => 'select',
    'class'             => '',
    'source'            => 'unl_payment/account_source',
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

$conn->addColumn($this->getTable('sales/quote_item'), 'unl_payment_account', 'int unsigned default NULL AFTER `source_store_view`');
$conn->addColumn($this->getTable('sales/order_item'), 'unl_payment_account', 'int unsigned default NULL AFTER `source_store_view`');

$installer->run("
DROP TABLE IF EXISTS `{$this->getTable('unl_payment/account')}`;
CREATE TABLE `{$this->getTable('unl_payment/account')}` (
    `account_id` int(10) unsigned NOT NULL auto_increment,
    `group_id` smallint(5) unsigned NOT NULL,
    `name` varchar(255) NOT NULL default '',
    PRIMARY KEY  (`account_id`),
    KEY `FK_UNL_PAYMENT_ACCOUNT_GROUP` (`group_id`),
    CONSTRAINT `FK_UNL_PAYMENT_ACCOUNT_GROUP` FOREIGN KEY (`group_id`) REFERENCES `{$this->getTable('core/store_group')}` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
