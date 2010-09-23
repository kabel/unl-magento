<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$exemptOrg = Mage::getModel('tax/class')->getCollection()
                ->setClassTypeFilter('CUSTOMER')
                ->addFieldToFilter('class_name', 'Exempt Org')
                ->getFirstItem();

$installer->run("
INSERT INTO `{$installer->getTable('customer_group')}` (`customer_group_code`, `tax_class_id`) VALUES ('UNL Student - Fee Paying', 3), ('UNL Cost Object Authorized', {$exemptOrg->getId()});
");

$installer->endSetup();
