<?php

$installer = $this;
/* @var $installer Mage_Eav_Model_Entity_Setup */

$installer->startSetup();

$setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
$setup->addAttribute('catalog_category', 'group_acl', array(
    'type'              => 'text',
    'backend'           => 'unl_core/catalog_category_attribute_backend_groupacl',
    'frontend'          => '',
    'label'             => 'Only Allow Access To',
    'input'             => 'multiselect',
    'input_renderer'    => 'unl_core/adminhtml_catalog_category_helper_groupacl',
    'source'            => 'unl_core/catalog_category_attribute_source_groupacl',
    'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'           => true,
    'required'          => false,
    'user_defined'      => false,
    'default'           => false,
    'unique'            => false,
    'is_configurable'   => true
));

$entityTypeId     = $installer->getEntityTypeId('catalog_category');
$attributeSetId   = $installer->getDefaultAttributeSetId($entityTypeId);
$attributeGroupId = $installer->getAttributeGroupId($entityTypeId, $attributeSetId, 'Display Settings');

$installer->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, 'group_acl', 60);

$installer->endSetup();
