<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;

/**
 * install tax classes
 */
$data = array(
    array(
        'class_name' => 'Force Lincoln Taxable',
        'class_type' => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
    ),
    array(
        'class_name' => 'Force Lincoln Restaurant Taxable',
        'class_type' => Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT
    ),
);
$installer->getConnection()->insertMultiple($installer->getTable('tax/tax_class'), $data);
