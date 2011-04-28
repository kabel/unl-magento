<?php
$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();
$installer->run("
-- DROP TABLE IF EXISTS `{$installer->getTable('comm_queue')}`;
CREATE TABLE `{$installer->getTable('comm_queue')}` (
  `queue_id` int(7) unsigned NOT NULL auto_increment,
  `queue_status` int(3) unsigned NOT NULL default '0',
  `queue_start_at` datetime default NULL,
  `queue_finish_at` datetime default NULL,
  `message_type` int(3) default NULL,
  `message_text` text,
  `message_styles` text,
  `message_subject` varchar(300) default NULL,
  `message_sender_name` varchar(200) default NULL,
  `message_sender_email` varchar(200) character set latin1 collate latin1_general_ci default NULL,
  PRIMARY KEY  (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Communication queue';

-- DROP TABLE IF EXISTS `{$installer->getTable('comm_queue_link')}`;
CREATE TABLE `{$installer->getTable('comm_queue_link')}` (
  `queue_link_id` int(9) unsigned NOT NULL auto_increment,
  `queue_id` int(7) unsigned NOT NULL default '0',
  `customer_id` int(7) unsigned NOT NULL default '0',
  `sent_at` datetime default NULL,
  PRIMARY KEY  (`queue_link_id`),
  KEY `FK_COMM_QUEUE_LINK_CUSTOMER` (`customer_id`),
  KEY `FK_COMM_QUEUE_LINK_QUEUE` (`queue_id`),
  KEY `IDX_COMM_QUEUE_LINK_SENT_AT` (`queue_id`,`sent_at`),
  CONSTRAINT `FK_COMM_QUEUE_LINK_QUEUE` FOREIGN KEY (`queue_id`) REFERENCES `{$installer->getTable('comm_queue')}` (`queue_id`) ON DELETE CASCADE,
  CONSTRAINT `FK_COMM_QUEUE_LINK_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Communication queue to customer link';
");
$installer->endSetup();
