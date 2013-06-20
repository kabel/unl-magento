<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_core/tax_boundary'))
    ->addColumn('record_type', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
            'nullable'  => false,
        ),
        'Record Type'
    )
    ->addColumn('begin_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
            'nullable'  => false,
        ),
        'Begin Date'
    )
    ->addColumn('end_date', Varien_Db_Ddl_Table::TYPE_DATE, null, array(
            'nullable'  => false,
        ),
        'End Date'
    )
    ->addColumn('low_address_range', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Low Address Range'
    )
    ->addColumn('high_address_range', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'High Address Range'
    )
    ->addColumn('odd_even_indicator', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
            'default'   => null,
        ),
        'Odd Even Indicator'
    )
    ->addColumn('street_pre_directional', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Street Pre Directional'
    )
    ->addColumn('street_name', Varien_Db_Ddl_Table::TYPE_TEXT, 20, array(
            'default'   => null,
        ),
        'Street Name'
    )
    ->addColumn('street_suffix_abbr', Varien_Db_Ddl_Table::TYPE_TEXT, 4, array(
            'default'   => null,
        ),
        'Street Suffix Abbreviation'
    )
    ->addColumn('street_post_directional', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Street Post Directional'
    )
    ->addColumn('address_secondary_abbr', Varien_Db_Ddl_Table::TYPE_TEXT, 4, array(
            'default'   => null,
        ),
        'Address Secondary Abbreviation'
    )
    ->addColumn('address_secondary_low', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
            'default'   => null,
        ),
        'Address Secondary Low'
    )
    ->addColumn('address_secondary_high', Varien_Db_Ddl_Table::TYPE_TEXT, 8, array(
            'default'   => null,
        ),
        'Address Secondary High'
    )
    ->addColumn('address_secondary_odd_even', Varien_Db_Ddl_Table::TYPE_TEXT, 1, array(
            'default'   => null,
        ),
        'Address Secondary Odd Even'
    )
    ->addColumn('city_name', Varien_Db_Ddl_Table::TYPE_TEXT, 28, array(
            'default'   => null,
        ),
        'City Name'
    )
    ->addColumn('zip_code', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Zip Code'
    )
    ->addColumn('plus_4', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Plus 4'
    )
    ->addColumn('zip_code_low', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Zip Code Low'
    )
    ->addColumn('zip_ext_low', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Zip Ext Low'
    )
    ->addColumn('zip_code_high', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Zip Code High'
    )
    ->addColumn('zip_ext_high', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'default'   => null,
        ),
        'Zip Ext High'
    )
    ->addColumn('composite_ser_code', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Composite Serial Code'
    )
    ->addColumn('fips_state_code', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'FIPS State Code'
    )
    ->addColumn('fips_state_indicator', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'FIPS State Indicator'
    )
    ->addColumn('fips_county_code', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
            'default'   => null,
        ),
        'FIPS County Code'
    )
    ->addColumn('fips_place_number', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'FIPS Place Number'
    )
    ->addColumn('fips_place_class_code', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'FIPS Place Class Code'
    )
    ->addColumn('longitude', Varien_Db_Ddl_Table::TYPE_DECIMAL, '10,7', array(
            'default'   => null,
        ),
        'Longitude'
    )
    ->addColumn('latitude', Varien_Db_Ddl_Table::TYPE_DECIMAL, '10,7', array(
            'default'   => null,
        ),
        'Latitude'
    )
    ->addColumn('spcl_tax_dist_code_src_1', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_1', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_1', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_2', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_2', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_2', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_3', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_3', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_3', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_4', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_4', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_4', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_5', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_5', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_5', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_6', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_6', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_6', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_7', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_7', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_7', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_8', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_8', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_8', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_9', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_9', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_9', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_10', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_10', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_10', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_11', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_11', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_11', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_12', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_12', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_12', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_13', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_13', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_13', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_14', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_14', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_14', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_15', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_15', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_15', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_16', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_16', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_16', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_17', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_17', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_17', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_18', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_18', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_18', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_19', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_19', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_19', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('spcl_tax_dist_code_src_20', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,
        ),
        'Special Tax District Code Source'
    )
    ->addColumn('spcl_tax_dist_code_20', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'default'   => null,
        ),
        'Special Tax District Code'
    )
    ->addColumn('tax_auth_type_code_20', Varien_Db_Ddl_Table::TYPE_TEXT, 2, array(
            'default'   => null,

        ),
        'Tax Auth Type Code'
    )
    ->addColumn('boundary_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'primary'   => true,
            'identity'  => true,
            'nullable'  => false,
        ),
        'Boundary Id'
    )
    ->setComment('UNL Tax Boundaries');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_core/tax_counties'))
    ->addColumn('county_id', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
            'primary'   => true,
            'nullable'  => false,
        ),
        'County Id'
    )
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Name'
    )
    ->setComment('UNL Tax Counties');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_core/tax_places'))
    ->addColumn('fips_place_number', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'primary'   => true,
            'nullable'  => false,
        ),
        'FIPS Place Number'
    )
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Name'
    )
    ->setComment('UNL Tax Places');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_core/warehouse'))
    ->addColumn('warehouse_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'primary'   => true,
            'identity'  => true,
            'nullable'  => false,
        ),
        'Warehouse Id'
    )
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Name'
    )
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Email Address'
    )
    ->setComment('UNL Warehouses');
$installer->getConnection()->createTable($table);

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');

/**
 * Hide system catalog_product attributes that UNL does not use
 */
$catalogInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_from', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_to', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'page_layout', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_recurring', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'recurring_profile', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'enable_googlecheckout', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design_from', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design_to', 'is_visible', false);

/**
 * Add source_store_view attribute to the 'eav/attribute' table
 */
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'source_store_view', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Source Store',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'unl_core/store_source_switcher',
    'global'                     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'visible'                    => true,
    'required'                   => true,
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
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'enable_featured', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Allow product to be featured',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'eav/entity_attribute_source_boolean',
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
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'featured_from', array(
    'type'                       => 'datetime',
    'backend'                    => 'eav/entity_attribute_backend_datetime',
    'frontend'                   => '',
    'label'                      => 'Feature From',
    'input'                      => 'date',
    'class'                      => '',
    'source'                     => '',
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
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'featured_to', array(
    'type'                       => 'datetime',
    'backend'                    => 'eav/entity_attribute_backend_datetime',
    'frontend'                   => '',
    'label'                      => 'Feature To',
    'input'                      => 'date',
    'class'                      => '',
    'source'                     => '',
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
));

$catalogInstaller->addAttribute('catalog_category', 'group_acl', array(
    'type'                       => 'text',
    'backend'                    => 'unl_core/catalog_category_attribute_backend_groupacl',
    'frontend'                   => '',
    'label'                      => 'Only Allow Access To',
    'input'                      => 'multiselect',
    'input_renderer'             => 'unl_core/adminhtml_catalog_category_helper_groupacl',
    'class'                      => '',
    'source'                     => 'unl_core/catalog_category_attribute_source_groupacl',
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
    'group'                      => 'Display Settings',
    'sort_order'                 => 60,
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'product_group_acl', array(
    'type'                       => 'text',
    'backend'                    => 'unl_core/catalog_category_attribute_backend_groupacl',
    'frontend'                   => '',
    'label'                      => 'Only Allow Access To',
    'input'                      => 'multiselect',
    'input_renderer'             => 'unl_core/adminhtml_catalog_product_helper_groupacl',
    'class'                      => '',
    'source'                     => 'unl_core/catalog_category_attribute_source_groupacl',
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
    'group'                      => 'Security',
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'limit_sale_qty', array(
    'type'                       => 'decimal',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Maximum Qty Allowed to Purchase',
    'input'                      => 'text',
    'class'                      => '',
    'source'                     => '',
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
    'group'                      => 'Security',
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'no_sale', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Disable Sale',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'eav/entity_attribute_source_boolean',
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
    'used_in_product_listing'    => true,
    'unique'                     => false,
    'note'                       => 'ENABLING this will display a message to the customer, that they cannot purchase online',
    'apply_to'                   => 'simple',
));

$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'warehouse', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Warehouse',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'unl_core/warehouse_source',
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
    'used_in_product_listing'    => true,
    'unique'                     => false,
    'group'                      => 'Shipping',
    'apply_to'                   => 'simple,configurable,bundle',
));

$catalogInstaller->addAttribute('catalog_category', 'is_alpha_list', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Is Alpha Listing',
    'input'                      => 'select',
    'class'                      => '',
    'source'                     => 'eav/entity_attribute_source_boolean',
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
    'group'                      => 'Display Settings',
    'sort_order'                 => 35,
));

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order'),
    'external_id',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 50,
        'nullable'  => false,
        'default'   => '',
        'comment'   => 'External Reference'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/order'),
    $installer->getIdxName($installer->getTable('sales/order'), array('external_id')),
    array('external_id')
);

$salesInstaller = Mage::getResourceModel('sales/setup', 'sales_setup');
$salesInstaller->addAttribute('order', 'external_id', array('type' => 'static'));

$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'scope',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '1M',
        'comment'   => 'Scope'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('admin/user'),
    'is_cas',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Is UNL CAS'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/quote_item'),
    'source_store_view',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'Source Store'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales/quote_item'),
    'warehouse',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'Warehouse'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'),
    'is_dummy',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Is Dummy'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'),
    'source_store_view',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'Source Store'
    )
);
$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_item'),
    'warehouse',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
        'unsigned'  => true,
        'comment'   => 'Warehouse'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/creditmemo_item'),
    'is_dummy',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Is Dummy'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/invoice_item'),
    'is_dummy',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Is Dummy'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/shipment_item'),
    'is_dummy',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'comment'   => 'Is Dummy'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_tax'),
    'sale_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'nullable'  => false,
        'comment'   => 'Sale Amount'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/order_tax'),
    'base_sale_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'nullable'  => false,
        'comment'   => 'Base Sale Amount'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('tax/tax_order_aggregated_created'),
    'base_sales_amount_sum',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'nullable'  => false,
        'comment'   => 'Base Sale Amount'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('tax/tax_order_aggregated_updated'),
    'base_sales_amount_sum',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'nullable'  => false,
        'comment'   => 'Base Sale Amount'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('core/store_group'),
    'is_hidden',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'unsigned'  => true,
        'nullable'  => false,
        'default'   => '0',
        'comment'   => 'Is Hidden'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('core/store_group'),
    'description',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '64k',
        'nullable'  => false,
        'default'   => '',
        'comment'   => 'Description'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('cms/page'),
    'permissions',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '1M',
        'comment'   => 'Permissions'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/creditmemo'),
    'refunded_at',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'comment'   => 'Refunded At'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/creditmemo'),
    $installer->getIdxName($installer->getTable('sales/creditmemo'), array('refunded_at')),
    array('refunded_at')
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/invoice'),
    'paid_at',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'comment'   => 'Paid At'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/invoice'),
    $installer->getIdxName($installer->getTable('sales/creditmemo'), array('paid_at')),
    array('paid_at')
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/creditmemo_grid'),
    'refunded_at',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'comment'   => 'Refunded At'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/creditmemo_grid'),
    $installer->getIdxName($installer->getTable('sales/creditmemo_grid'), array('refunded_at')),
    array('refunded_at')
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/invoice_grid'),
    'paid_at',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
        'comment'   => 'Paid At'
    )
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/invoice_grid'),
    $installer->getIdxName($installer->getTable('sales/invoice_grid'), array('paid_at')),
    array('paid_at')
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/shipment_grid'),
    'shipping_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Shipping Amount'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/shipment_grid'),
    'shipping_description',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => 255,
        'comment'   => 'Shipping Description'
    )
);

$installer->getConnection()->addColumn(
    $installer->getTable('sales/shipment_grid'),
    'base_shipping_amount',
    array(
        'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
        'scale'     => 4,
        'precision' => 12,
        'comment'   => 'Base Shipping Amount'
    )
);

$installer->endSetup();
