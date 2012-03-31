<?php

/* @var $installer Unl_CustomerTag_Model_Resource_Setup */
$installer = $this;

/**
 * install default customer tag
 */
$data = array(
    'name'        => 'Allow Invoicing',
    'is_system'   => 1
);
$installer->getConnection()->insert($installer->getTable('unl_customertag/tag'), $data);
