<?php

/* @var $installer Unl_PaymentFilter_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'payment_filter', array(
    'type'                       => 'text',
    'backend'                    => 'eav/entity_attribute_backend_array',
    'frontend'                   => '',
    'label'                      => 'Disallow Payment Method',
    'input'                      => 'multiselect',
    'input_renderer'             => '',
    'class'                      => '',
    'source'                     => 'unl_paymentfilter/catalog_product_attribute_source_payments',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => true,
    'required'                   => false,
    'user_defined'               => false,
    'default'                    => '',
    'searchable'                 => false,
    'filterable'                 => false,
    'comparable'                 => false,
    'is_configurable'            => false,
    'visible_on_front'           => false,
    'visible_in_advanced_search' => false,
    'used_in_product_listing'    => false,
    'unique'                     => false,
    'group'                      => 'Prices',
    'note'                       => 'Leave empty to allow all system enabled payment methods.'
));

$installer->endSetup();
