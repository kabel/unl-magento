<?php

/* @var $installer Unl_Ship_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_ship/shipment_package'))
    ->addColumn('package_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'identity'  => true,
            'primary'   => true,
            'nullable'  => false,
        ),
        'Package Id'
    )
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Order Id'
    )
    ->addColumn('shipment_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Shipment Id'
    )
    ->addColumn('carrier', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
            'nullable'  => false,
        ),
        'Shipping Carrier'
    )
    ->addColumn('carrier_shipment_id', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
            'nullable'  => false,
        ),
        'Carrier Shipment Id'
    )
    ->addColumn('weight_units', Varien_Db_Ddl_Table::TYPE_TEXT, 3, array(
            'nullable'  => false,
        ),
        'Weight Units'
    )
    ->addColumn('weight', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(
            'nullable'  => false,
        ),
        'Weight'
    )
    ->addColumn('tracking_number', Varien_Db_Ddl_Table::TYPE_TEXT, 50, array(
            'nullable'  => false,
        ),
        'Tracking Number'
    )
    ->addColumn('currency_units', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'nullable'  => false,
        ),
        'Currency Units'
    )
    ->addColumn('transportation_charge', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(),
        'Transportantion Charge'
    )
    ->addColumn('service_option_charge', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(),
        'Service Option Charge'
    )
    ->addColumn('shipping_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(),
        'Shipping Total'
    )
    ->addColumn('negotiated_total', Varien_Db_Ddl_Table::TYPE_DECIMAL, '12,4', array(),
        'Negotiated Total'
    )
    ->addColumn('label_format', Varien_Db_Ddl_Table::TYPE_TEXT, 5, array(
            'nullable'  => false,
        ),
        'Label Format'
    )
    ->addColumn('label_image', Varien_Db_Ddl_Table::TYPE_VARBINARY, '2M', array(
            'nullable'  => false,
        ),
        'Label Image'
    )
    ->addColumn('html_label_image', Varien_Db_Ddl_Table::TYPE_VARBINARY, '2M', array(),
        'Label HTML Wrapper'
    )
    ->addColumn('ins_doc', Varien_Db_Ddl_Table::TYPE_VARBINARY, '2M', array(),
        'Insurance Document'
    )
    ->addColumn('intl_doc', Varien_Db_Ddl_Table::TYPE_VARBINARY, '2M', array(),
        'Customs Forms'
    )
    ->addColumn('date_shipped', Varien_Db_Ddl_Table::TYPE_DATETIME, null, array(
            'nullable'  => false,
            'default'   => '0000-00-00',
        ),
        'Date Shipped'
    )
    ->setComment('Shipment Package Labels');
$installer->getConnection()->createTable($table);

/**
 * Add ships_separately attribute to the 'eav/attribute' table
 */
$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'ships_separately', array(
    'type'                       => 'int',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Ships Separately',
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
    'apply_to'                   => 'simple,configurable',
    'group'                      => 'Shipping',
    'sort_order'                 => 10
));

$installer->endSetup();