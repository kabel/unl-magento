<?php

/* @var $installer Unl_Ship_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Drop foreign keys
 */
$installer->getConnection()->dropForeignKey(
    $installer->getTable('unl_ship/shipment_package'),
    'FK_SHIPMENT_PACKAGE_SHIPMENT'
);

/**
 * Drop indexes
 */
$installer->getConnection()->dropIndex(
    $installer->getTable('unl_ship/shipment_package'),
    'IDX_ORDER'
);

/**
 * Change columns
 */
$tables = array(
    $installer->getTable('unl_ship/shipment_package') => array(
        'columns' => array(
            'package_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'identity'  => true,
                'primary'   => true,
                'nullable'  => false,
                'comment'   => 'Package Id'
            ),
            'order_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Order Id'
            ),
            'shipment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_INTEGER,
                'unsigned'  => true,
                'nullable'  => false,
                'comment'   => 'Shipment Id'
            ),
            'carrier' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 255,
                'nullable'  => false,
                'comment'   => 'Shipping Carrier'
            ),
            'carrier_shipment_id' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'nullable'  => false,
                'comment'   => 'Carrier Shipment Id'
            ),
            'weight_units' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 3,
                'nullable'  => false,
                'comment'   => 'Weight Units'
            ),
            'weight' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'nullable'  => false,
                'comment'   => 'Weight'
            ),
            'tracking_number' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 50,
                'nullable'  => false,
                'comment'   => 'Tracking Number'
            ),
            'currency_units' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'nullable'  => false,
                'comment'   => 'Currency Units'
            ),
            'transportation_charge' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Transportantion Charge'
            ),
            'service_option_charge' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Service Option Charge'
            ),
            'shipping_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Shipping Total'
            ),
            'negotiated_total' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DECIMAL,
                'scale'     => 4,
                'precision' => 12,
                'comment'   => 'Negotiated Total'
            ),
            'label_format' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
                'length'    => 5,
                'nullable'  => false,
                'comment'   => 'Label Format'
            ),
            'label_image' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_VARBINARY,
                'length'    => '2M',
                'nullable'  => false,
                'comment'   => 'Label Image'
            ),
            'html_label_image' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_VARBINARY,
                'length'    => '2M',
                'comment'   => 'Label HTML Wrapper'
            ),
            'ins_doc' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_VARBINARY,
                'length'    => '2M',
                'comment'   => 'Insurance Document'
            ),
            'intl_doc' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_VARBINARY,
                'length'    => '2M',
                'comment'   => 'Customs Forms'
            ),
            'date_shipped' => array(
                'type'      => Varien_Db_Ddl_Table::TYPE_DATETIME,
                'nullable'  => false,
                'default'   => '0000-00-00',
                'comment'   => 'Date Shipped'
            ),
        ),
        'comment' => 'Shipment Package Labels'
    )
);
$installer->getConnection()->modifyTables($tables);

/**
 * Add indexes
 */
$installer->getConnection()->addIndex(
    $installer->getTable('unl_ship/shipment_package'),
    $installer->getIdxName('unl_ship/shipment_package', array('order_id')),
    array('order_id')
);

/**
 * Add foreign keys
 */
$installer->getConnection()->addForeignKey(
    $installer->getFkName('unl_ship/shipment_package', 'shipment_id', 'sales/shipment', 'entity_id'),
    $installer->getTable('unl_ship/shipment_package'),
    'shipment_id',
    $installer->getTable('sales/shipment'),
    'entity_id'
);

$installer->endSetup();
