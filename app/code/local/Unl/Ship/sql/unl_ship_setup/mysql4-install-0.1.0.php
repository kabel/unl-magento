<?php

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("
DROP TABLE IF EXISTS {$installer->getTable('unl_ship/shipment_package')};
CREATE TABLE {$installer->getTable('unl_ship/shipment_package')} (
  `package_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `order_id` INT(10) UNSIGNED NOT NULL ,
  `shipment_id` INT (10) UNSIGNED DEFAULT NULL,
  `carrier` ENUM( 'dhl', 'fedex', 'ups', 'usps' ) NOT NULL,
  `carrier_shipment_id` VARCHAR( 50 ) NOT NULL,
  `weight_units` VARCHAR (3) NOT NULL,
  `weight` DECIMAL(12,4) NOT NULL,
  `tracking_number` VARCHAR( 50 ) NOT NULL,
  `currency_units` VARCHAR(5) NOT NULL,
  `transporation_charge` DECIMAL(12,4) NULL,
  `service_option_charge` DECIMAL(12,4) NULL,
  `shipping_total` DECIMAL(12,4) NOT NULL,
  `negotiated_total` DECIMAL(12,4) NULL,
  `label_format` VARCHAR( 5 ) NOT NULL,
  `label_image` BLOB NOT NULL,
  `html_label_image` BLOB NULL,
  `ins_doc` BLOB NULL,
  `intl_doc` BLOB NULL,
  `date_shipped` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`package_id`),
  KEY `IDX_ORDER` (`order_id`),
  CONSTRAINT `FK_SHIPMENT_PACKAGE_SHIPMENT` FOREIGN KEY (`shipment_id`)
    REFERENCES `{$installer->getTable('sales/shipment')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$installer->endSetup();
