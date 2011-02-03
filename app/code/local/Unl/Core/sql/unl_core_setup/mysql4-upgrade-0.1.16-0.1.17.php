<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('admin/user'), 'is_cas', 'TINYINT(1) NOT NULL DEFAULT 0');

$installer->endSetup();
