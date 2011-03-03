<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$installer->getConnection()->addColumn($this->getTable('sales/creditmemo'), 'refunded_at', 'datetime default NULL AFTER updated_at');
$installer->getConnection()->addColumn($this->getTable('sales/invoice'), 'paid_at', 'datetime default NULL AFTER updated_at');

$installer->run("
	UPDATE {$this->getTable('sales/creditmemo')}
	SET `refunded_at` = `created_at`
	WHERE `state` = " . Mage_Sales_Model_Order_Creditmemo::STATE_REFUNDED
);

$installer->run("
	UPDATE {$this->getTable('sales/invoice')}
	SET `paid_at` = `created_at`
	WHERE `state` = " . Mage_Sales_Model_Order_Invoice::STATE_PAID
);

$installer->endSetup();
