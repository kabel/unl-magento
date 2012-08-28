<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_from', 'is_visible', true)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_to', 'is_visible', true)
    ->updateAttribute('catalog_category', 'custom_design_from', 'is_visible', true)
    ->updateAttribute('catalog_category', 'custom_design_to', 'is_visible', true);

$installer->endSetup();
