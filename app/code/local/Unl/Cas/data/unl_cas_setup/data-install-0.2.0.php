<?php

/* @var $installer Unl_Cas_Model_Resource_Setup */
$installer = $this;

/**
 * install UNL supplied customer tags
 */
$data = array(
    array(
        'name'        => Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT,
        'is_system'   => 1
    ),
    array(
        'name'        => Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT_FEES,
        'is_system'   => 1
    ),
    array(
        'name'        => Unl_Cas_Helper_Data::CUSTOMER_TAG_FACULTY_STAFF,
        'is_system'   => 1
    ),
);
$installer->getConnection()->insertMultiple($installer->getTable('unl_customertag/tag'), $data);
