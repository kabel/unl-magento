<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('cms/page'), 'permissions', 'text default NULL');

$installer->endSetup();
