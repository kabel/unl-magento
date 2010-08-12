<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

$installer->run("
INSERT INTO `{$installer->getTable('customer_group')}` (`customer_group_code`, `tax_class_id`) VALUES ('UNL Student', 3), ('UNL Faculty/Staff', 3);
");
$installer->addAttribute('customer', 'unl_cas_uid', array(
    'label'        => 'UNL CAS UID',
    'visible'      => 0,
    'required'     => 0
));

$installer->endSetup();
