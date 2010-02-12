<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/order_tax'), 'sale_amount', 'DECIMAL(12,4) NOT NULL');
$installer->getConnection()->addColumn($this->getTable('sales/order_tax'), 'base_sale_amount', 'DECIMAL(12,4) NOT NULL');

$installer->endSetup();
