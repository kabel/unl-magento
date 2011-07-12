<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('unl_adminlog/log')};
CREATE TABLE {$this->getTable('unl_adminlog/log')} (
  `log_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `created_at` DATETIME NOT NULL ,
  `remote_addr` bigint(20) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL ,
  `event_module` varchar(255) NOT NULL ,
  `action` SMALLINT(5) unsigned NOT NULL DEFAULT '0' ,
  `result` TINYINT(1) unsigned NOT NULL DEFAULT '0' ,
  `action_path` varchar(255) NOT NULL ,
  `action_info` TEXT DEFAULT NULL ,
  `is_archived` TINYINT(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `IDX_UNL_ADMINLOG_ARCHIVED` (`is_archived`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Administrtor Action Log';

");

$installer->endSetup();
