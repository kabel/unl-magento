<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'ordered_description', array(
    'type'                       => 'text',
    'label'                      => 'Ordered Instructions',
    'input'                      => 'textarea',
    'sort_order'                 => 20,
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'required'                   => false,
    'is_configurable'            => false,
    'wysiwyg_enabled'            => true,
    'note'                       => 'This will be displayed in the order confirmation message to the customer',
    'group'                      => 'Description',
));

$installer->endSetup();
