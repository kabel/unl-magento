<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('tax/tax_order_aggregated_created'), 'base_sales_amount_sum', 'FLOAT(12,4) NOT NULL DEFAULT 0.0000');

$installer->endSetup();
