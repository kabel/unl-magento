<?php

/* @var $installer Mage_Eav_Model_Entity_Setup */
$installer = $this;
$installer->getConnection()->addColumn($installer->getTable('core/store'), 'unl_rate', "DECIMAL(12,4) UNSIGNED NOT NULL DEFAULT '0.0000'");
