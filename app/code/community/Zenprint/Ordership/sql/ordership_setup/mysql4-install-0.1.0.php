<?php
/**
 * Zenprint
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zenprint.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2009 ZenPrint (http://www.zenprint.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
/* @var $installer Mage_Core_Model_Resource_Setup */

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('shipping_shipment_package')};
CREATE TABLE {$this->getTable('shipping_shipment_package')} (
  `package_id` INT( 15 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
  `order_increment_id` INT( 15 ) NOT NULL ,
  `order_shipment_id` INT (15) NOT NULL,
  `carrier` ENUM( 'dhl', 'fedex', 'ups', 'usps' ) NOT NULL ,
  `carrier_shipment_id` VARCHAR( 50 ) NOT NULL ,
  `weight_units` VARCHAR (3) NOT NULL,
  `weight` DECIMAL(12,4) NOT NULL,
  `tracking_number` VARCHAR( 50 ) NOT NULL ,
  `currency_units` VARCHAR(5) NOT NULL,
  `transporation_charge` DECIMAL(12,4) NULL,
  `service_option_charge` DECIMAL(12,4) NULL,
  `shipping_total` DECIMAL(12,4) NOT NULL,
  `negotiated_total` DECIMAL(12,4) NULL,
  `label_format` VARCHAR( 5 ) NOT NULL ,
  `label_image` BLOB NOT NULL ,
  `html_label_image` BLOB NULL ,
  `date_shipped` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
INDEX ( `package_id` ),
INDEX ( `order_increment_id`, `order_shipment_id` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
//$this->getConnection()->addConstraint('FK_SHIPPING_SHIPMENT_PACKAGE_ORDER_SHIPMENT_ID',
//    $this->getTable('shipping_shipment_package'), 'order_increment_id',
//    $this->getTable('sales_order'), 'increment_id'
//);

$installer->endSetup();

?>