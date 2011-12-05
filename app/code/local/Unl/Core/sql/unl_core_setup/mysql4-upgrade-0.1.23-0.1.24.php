<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->getConnection()->dropColumn($installer->getTable('core/store'), 'unl_rate');
$installer->getConnection()->addColumn($installer->getTable('core/store_group'), 'is_hidden', 'TINYINT(1) UNSIGNED DEFAULT 0');
