<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->getConnection()->addColumn($installer->getTable('sales/order'), 'external_id', "VARCHAR(50) NOT NULL DEFAULT ''");
$installer->getConnection()->addKey($installer->getTable('sales/order'), 'IDX_EXTERNAL_ID', 'external_id');
$installer->addAttribute('order', 'external_id', array('type'=>'static'));
