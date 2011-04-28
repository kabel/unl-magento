<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_grid'),
    'shipping_description', 'VARCHAR(255) DEFAULT NULL');

$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_grid'),
    'base_shipping_amount', 'DECIMAL(12,4) DEFAULT NULL');
$installer->getConnection()->addColumn($installer->getTable('sales_flat_shipment_grid'),
    'shipping_amount', 'DECIMAL(12,4) DEFAULT NULL');

//Update existing shipment grid
$select = $installer->getConnection()->select();
$select->join(
    array('order' => $installer->getTable('sales_flat_order')),
    'order.entity_id = e.order_id',
    array('shipping_description', 'base_shipping_amount', 'shipping_amount')
);
$installer->run($select->crossUpdateFromSelect(array('e'=>$installer->getTable('sales_flat_shipment_grid'))));

$installer->endSetup();
