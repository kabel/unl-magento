<?php

/* @var $installer Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$installer = $this;

$installer->getConnection()->addColumn($installer->getTable('downloadable/link'), 'link_secret', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'size' => 255,
    'comment' => 'String used to generate callback signature via HMAC',
    'after' => 'link_url'
));

$installer->getConnection()->addColumn($installer->getTable('downloadable/link_purchased_item'), 'link_secret', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'size' => 255,
    'comment' => 'String used to generate callback signature via HMAC',
    'after' => 'link_url'
));
