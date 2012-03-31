<?php

/* @var $installer Unl_Notify_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$installer->run("
CREATE TABLE {$installer->getTable('unl_notify/order_queue')} (
  `queue_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `order_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'notify_emails', array(
	'type'              => 'text',
	'backend'           => '',
	'frontend'          => '',
	'label'             => 'Notify on Order',
	'input'             => 'text',
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
	'unique'            => false,
	'note'              => 'Comma-separated list of email addresses.'
));

$installer->endSetup();
