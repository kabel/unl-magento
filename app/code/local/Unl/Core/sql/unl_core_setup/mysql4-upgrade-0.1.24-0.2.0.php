<?php

/* @var $installer Unl_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    'IX_BOUNDARY_RECORD_TYPE'
);
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    'IX_BOUNDARY_CITY_STREET'
);
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_core/tax_boundary'),
    'IX_BOUNDARY_ZIP_STREET_NAME'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('sales/invoice'),
    'IDX_PAID_AT'
);
$installer->getConnection()->dropIndex(
    $installer->getTable('sales/invoice_grid'),
    'IDX_PAID_AT'
);

$installer->getConnection()->dropIndex(
    $installer->getTable('sales/creditmemo'),
    'IDX_REFUNDED_AT'
);
$installer->getConnection()->dropIndex(
    $installer->getTable('sales/creditmemo_grid'),
    'IDX_REFUNDED_AT'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_core/tax_boundary') => array(
        'columns' => array (
            'record_type' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 1,
                'nullable'  => false,
                'comment'   => 'Record Type'
            ),
            'begin_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'nullable'  => false,
                'comment'   => 'Begin Date'
            ),
            'end_date' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATE,
                'nullable'  => false,
                'comment'   => 'End Date'
            ),
            'low_address_range' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Low Address Range'
            ),
            'high_address_range' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'High Address Range'
            ),
            'odd_even_indicator' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 1,
                'default'   => null,
                'comment'   => 'Odd Even Indicator'
            ),
            'street_pre_directional' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Street Pre Directional'
            ),
            'street_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 20,
                'default'   => null,
                'comment'   => 'Street Name'
            ),
            'street_suffix_abbr' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 4,
                'default'   => null,
                'comment'   => 'Street Suffix Abbreviation'
            ),
            'street_post_directional' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Street Post Directional'
            ),
            'address_secondary_abbr' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 4,
                'default'   => null,
                'comment'   => 'Address Secondary Abbreviation'
            ),
            'address_secondary_low' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 8,
                'default'   => null,
                'comment'   => 'Address Secondary Low'
            ),
            'address_secondary_high' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 8,
                'default'   => null,
                'comment'   => 'Address Secondary High'
            ),
            'address_secondary_odd_even' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 1,
                'default'   => null,
                'comment'   => 'Address Secondary Odd Even'
            ),
            'city_name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 28,
                'default'   => null,
                'comment'   => 'City Name'
            ),
            'zip_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Zip Code'
            ),
            'plus_4' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Plus 4'
            ),
            'zip_code_low' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Zip Code Low'
            ),
            'zip_ext_low' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Zip Ext Low'
            ),
            'zip_code_high' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Zip Code High'
            ),
            'zip_ext_high' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'default'   => null,
                'comment'   => 'Zip Ext High'
            ),
            'composite_ser_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Composite Serial Code'
            ),
            'fips_state_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'FIPS State Code'
            ),
            'fips_state_indicator' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'FIPS State Indicator'
            ),
            'fips_county_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'default'   => null,
                'comment'   => 'FIPS County Code'
            ),
            'fips_place_number' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'FIPS Place Number'
            ),
            'fips_place_class_code' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'FIPS Place Class Code'
            ),
            'longitude' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 7,
                'precision' => 10,
                'default'   => null,
                'comment'   => 'Longitude'
            ),
            'latitude' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 7,
                'precision' => 10,
                'default'   => null,
                'comment'   => 'Latitude'
            ),
            'spcl_tax_dist_code_src_1' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_1' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_1' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_2' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_2' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_2' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_3' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_3' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_3' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_4' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_4' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_4' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_5' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_5' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_5' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_6' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_6' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_6' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_7' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_7' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_7' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_8' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_8' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_8' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_9' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_9' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_9' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_10' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_10' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_10' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_11' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_11' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_11' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_12' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_12' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_12' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_13' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_13' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_13' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_14' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_14' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_14' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_15' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_15' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_15' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_16' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_16' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_16' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_17' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_17' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_17' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_18' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_18' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_18' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_19' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_19' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_19' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'spcl_tax_dist_code_src_20' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Special Tax District Code Source'
            ),
            'spcl_tax_dist_code_20' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'default'   => null,
                'comment'   => 'Special Tax District Code'
            ),
            'tax_auth_type_code_20' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 2,
                'default'   => null,
                'comment'   => 'Tax Auth Type Code'
            ),
            'boundary_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'primary'   => true,
                'identity'  => true,
                'nullable'  => false,
                'comment'   => 'Boundary Id'
            ),
        ),
        'comment' => 'UNL Tax Boundaries'
    ),
    $installer->getTable('unl_core/tax_counties') => array(
        'columns' => array(
            'county_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'primary'   => true,
                'nullable'  => false,
                'comment'   => 'County Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Name'
            )
        ),
        'comment' => 'UNL Tax Counties'
    ),
    $installer->getTable('unl_core/tax_places') => array(
        'columns' => array(
            'fips_place_number' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'primary'   => true,
                'nullable'  => false,
                'comment'   => 'FIPS Place Number'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Name'
            )
        ),
        'comment' => 'UNL Tax Places'
    ),
    $installer->getTable('unl_core/warehouse') => array(
        'columns' => array(
            'warehouse_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'primary'   => true,
                'identity'  => true,
                'nullable'  => false,
                'comment'   => 'Warehouse Id'
            ),
            'name' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Name'
            ),
            'email' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Email Address'
            ),
        ),
        'comment' => 'UNL Warehouses'
    ),

    $installer->getTable('admin/user') => array(
        'columns' => array(
            'scope' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'comment'   => 'Scope'
            ),
            'is_cas' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is UNL CAS'
            ),
            'warehouse_scope' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'comment'   => 'Warehouse Scope'
            )
        )
    ),

    $installer->getTable('sales/quote_item') => array(
        'columns' => array(
            'source_store_view' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Source Store'
            ),
            'warehouse' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Warehouse'
            )
        )
    ),

    $installer->getTable('sales/order_item') => array(
        'columns' => array(
            'source_store_view' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Source Store'
            ),
            'warehouse' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'comment'   => 'Warehouse'
            ),
            'is_dummy' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Is Dummy'
            )
        )
    ),

    $installer->getTable('sales/creditmemo_item') => array(
        'columns' => array(
            'is_dummy' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Is Dummy'
            )
        )
    ),

    $installer->getTable('sales/invoice_item') => array(
        'columns' => array(
            'is_dummy' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Is Dummy'
            )
        )
    ),

    $installer->getTable('sales/shipment_item') => array(
        'columns' => array(
            'is_dummy' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'comment'   => 'Is Dummy'
            )
        )
    ),

    $installer->getTable('sales/order_tax') => array(
        'columns' => array(
            'sale_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'comment'   => 'Sale Amount'
            ),
            'base_sale_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'comment'   => 'Base Sale Amount'
            )
        )
    ),

    $installer->getTable('tax/tax_order_aggregated_created') => array(
        'columns' => array(
            'base_sales_amount_sum' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'comment'   => 'Base Sale Amount'
            )
        )
    ),

    $installer->getTable('core/store_group') => array(
        'columns' => array(
            'is_hidden' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_SMALLINT,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Is Hidden'
            )
        )
    ),

    $installer->getTable('cms/page') => array(
        'columns' => array(
            'permissions' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => '1M',
                'comment'   => 'Permissions'
            )
        )
    ),

    $installer->getTable('sales/order') => array(
        'columns' => array(
            'external_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'nullable'  => false,
                'default'   => '',
                'comment'   => 'External Reference'
            )
        )
    ),

    $installer->getTable('sales/creditmemo') => array(
        'columns' => array(
            'refunded_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Refunded At'
            )
        )
    ),

    $installer->getTable('sales/invoice') => array(
        'columns' => array(
            'paid_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Paid At'
            )
        )
    ),

    $installer->getTable('sales/creditmemo_grid') => array(
        'columns' => array(
            'refunded_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Refunded At'
            )
        )
    ),

    $installer->getTable('sales/invoice_grid') => array(
        'columns' => array(
            'paid_at' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'comment'   => 'Paid At'
            )
        )
    ),

    $installer->getTable('sales/shipment_grid') => array(
        'columns' => array(
            'shipping_description' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'comment'   => 'Shipping Description'
            ),
            'base_shipping_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Base Shipping Amount'
            ),
            'shipping_amount' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Shipping Amount'
            )
        )
    ),
);
$installer->getConnection()->modifyTables($tables);

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

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('record_type')),
    array('record_type')
);
$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('city_name', 'street_name')),
    array('city_name', 'street_name')
);
$installer->getConnection()->addIndex(
    $installer->getTable('unl_core/tax_boundary'),
    $installer->getIdxName('unl_core/tax_boundary', array('zip_code', 'street_name')),
    array('zip_code', 'street_name')
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/invoice'),
    $installer->getIdxName('sales/invoice', array('paid_at')),
    array('paid_at')
);
$installer->getConnection()->addIndex(
    $installer->getTable('sales/invoice_grid'),
    $installer->getIdxName('sales/invoice_grid', array('paid_at')),
    array('paid_at')
);

$installer->getConnection()->addIndex(
    $installer->getTable('sales/creditmemo'),
    $installer->getIdxName('sales/creditmemo', array('refunded_at')),
    array('refunded_at')
);
$installer->getConnection()->addIndex(
    $installer->getTable('sales/creditmemo_grid'),
    $installer->getIdxName('sales/creditmemo_grid', array('refunded_at')),
    array('refunded_at')
);

$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');

/**
 * Hide system catalog_product attributes that UNL does not use
 */
$catalogInstaller->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_from', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'custom_design_from', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'page_layout', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'is_recurring', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'recurring_profile', 'is_visible', false)
    ->updateAttribute(Mage_Catalog_Model_Product::ENTITY, 'enable_googlecheckout', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design_from', 'is_visible', false)
    ->updateAttribute('catalog_category', 'custom_design_to', 'is_visible', false);

$installer->endSetup();
