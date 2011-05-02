<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/invoice_grid'), 'paid_at', 'datetime default NULL');
$installer->getConnection()->addColumn($this->getTable('sales/creditmemo_grid'), 'refunded_at', 'datetime default NULL');

// add indexes
$installer->getConnection()->addKey($this->getTable('sales/invoice'), 'IDX_PAID_AT', 'paid_at');
$installer->getConnection()->addKey($this->getTable('sales/creditmemo'), 'IDX_REFUNDED_AT', 'refunded_at');

$installer->getConnection()->addKey($this->getTable('sales/invoice_grid'), 'IDX_PAID_AT', 'paid_at');
$installer->getConnection()->addKey($this->getTable('sales/creditmemo_grid'), 'IDX_REFUNDED_AT', 'refunded_at');

$select = $installer->getConnection()->select();
$select->join(
    array('invoice' => $installer->getTable('sales/invoice')),
    'invoice.entity_id = e.entity_id',
    array('paid_at')
);

$installer->run($select->crossUpdateFromSelect(array('e'=>$installer->getTable('sales/invoice_grid'))));

$select = $installer->getConnection()->select();
$select->join(
    array('creditmemo' => $installer->getTable('sales/creditmemo')),
    'creditmemo.entity_id = e.entity_id',
    array('refunded_at')
);

$installer->run($select->crossUpdateFromSelect(array('e'=>$installer->getTable('sales/creditmemo_grid'))));


$installer->endSetup();
