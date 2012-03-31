<?php

/* @var $installer Unl_Notify_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('unl_notify/order_queue'))
    ->addColumn('queue_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,
            'identity'  => true,
        ),
        'Queue Id'
    )
    ->addColumn('order_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
            'unsigned'  => true,
            'nullable'  => false,
        ),
        'Order Id'
    )
    ->setComment('Orders queued to send notification');
$installer->getConnection()->createTable($table);

/**
 * Add notify_emails attribute to the 'eav/attribute' table
 */
$catalogInstaller = Mage::getResourceModel('catalog/setup', 'catalog_setup');
$catalogInstaller->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'notify_emails', array(
    'type'                       => 'text',
    'backend'                    => '',
    'frontend'                   => '',
    'label'                      => 'Notify of Order',
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
    'note'                       => 'Comma-separated list of email addresses.'
));

$installer->endSetup();
