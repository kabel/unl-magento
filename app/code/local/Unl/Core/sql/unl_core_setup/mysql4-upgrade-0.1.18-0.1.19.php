<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$exemptOrg = Mage::getModel('tax/class')->getCollection()
                ->setClassTypeFilter('CUSTOMER')
                ->addFieldToFilter('class_name', 'Exempt Org')
                ->getFirstItem();

$installer->run("
INSERT INTO `{$installer->getTable('customer/group')}` (`customer_group_code`, `tax_class_id`) VALUES ('Allow Invoicing', 3), ('Allow Invoicing - Exempt Org', {$exemptOrg->getId()});
");

$installer->endSetup();
