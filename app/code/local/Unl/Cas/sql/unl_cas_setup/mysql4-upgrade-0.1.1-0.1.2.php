<?php

$installer = $this;
/* @var $installer Mage_Customer_Model_Entity_Setup */

$installer->startSetup();

// FIX ISSUE WITH DEFAULT STORE LOAD
$store = Mage::app()->getStore();
$store->load(Mage_Core_Model_App::ADMIN_STORE_ID);

/* @var $eavConfig Mage_Eav_Model_Config */
$eavConfig = Mage::getSingleton('eav/config');
$attribute = $eavConfig->getAttribute('customer', 'unl_cas_uid');
$attribute->setWebsite($store->getWebsite());
$attribute->addData(array(
    'is_system' => 1,
    'sort_order' => 85,
    'used_in_forms' => array('adminhtml_customer')
));
$attribute->save();

$exemptOrg = Mage::getModel('tax/class')->getCollection()
    ->setClassTypeFilter('CUSTOMER')
    ->addFieldToFilter('class_name', 'Exempt Org')
    ->getFirstItem();

$installer->run("
INSERT INTO `{$installer->getTable('customer/group')}`
    (`customer_group_code`, `tax_class_id`) VALUES
    ('" . Unl_Cas_Helper_Data::CUSTOMER_GROUP_TAX_EXEMPT . "', {$exemptOrg->getId()})
;

INSERT INTO `{$installer->getTable('unl_customertag/tag')}`
    (`name`, `is_system`) VALUES
    ('" . Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT . "', 1),
    ('" . Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT_FEES . "', 1),
    ('" . Unl_Cas_Helper_Data::CUSTOMER_TAG_FACULTY_STAFF . "', 1)
;
");


$specialTagNames = array(
    Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT,
    Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT_FEES,
    Unl_Cas_Helper_Data::CUSTOMER_TAG_FACULTY_STAFF
);
$specialTags = Mage::getModel('unl_customertag/tag')->getCollection()
    ->addFieldToFilter('name', array('in' => $specialTagNames));

$configCollection = Mage::getModel('core/config_data')->getCollection()
    ->addFieldToFilter('path', 'customer/create_account/default_group')
    ->addFieldToFilter('scope', 'default');
$defaultGroup = $configCollection->getFirstItem()->getValue();

/* @var $groups Mage_Customer_Model_Entity_Group_Collection */
$groups = $group->getCollection();
$groups->addFieldToFilter('customer_group_code', array('in' => array(
    'Allow Invoicing',
	'Allow Invoicing - Exempt Org',
	'UNL Cost Object Authorized',
	'UNL Faculty/Staff',
	'UNL Student',
	'UNL Student - Fee Paying'
)));
$groupIds = $groups->getAllIds();

/* @var $customers Mage_Customer_Model_Entity_Customer_Collection */
$customers = Mage::getModel('customer/customer')->getCollection();
$customers->addFieldToFilter('group_id', array('in' => $groupIds))
    ->addAttributeToSelect('unl_cas_uid');
foreach ($customers as $customer) {
    if ($customer->getUnlCasUid()) {
        Mage::helper('unl_cas')->assignCustomerTags($customer, $customer->getUnlCasUid());
    }
    $customer->setGroupId($defaultGroup);
    $customer->save();
}
unset($customer);
unset($customers);

/* @var $products Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection */
$products = Mage::getModel('catalog/product')->getCollection();
$products->addAttributeToFilter('product_group_acl', array('neq' => ''));
foreach ($products as $product) {
    $newGroupAcl = array();
    $groupAcl = explode(',', $product->getProductGroupAcl());
    foreach ($groupAcl as $groupId) {
        if (!in_array($groupId, $groupIds)) {
            $newGroupAcl[] = $groupId;
        } else {
            $groupName = $groups->getItemById($groupId)->getCustomerGroupCode();
            if (in_array($groupName, $specialTagNames)) {
                $tag = $specialTags->getItemByColumnValue('name', $groupName);
                $tagIds = Mage::helper('unl_customertag')->getTagsByProduct($product)->getAllIds();
                if (!in_array($tag->getId(), $tagIds)) {
                    $tagIds[] = $tag->getId();
                }

                $product->setAccessTagIds($tagIds);
                $tag->getResource()->addProductLinks($product);
            }
        }
    }
    if (empty($newGroupAcl)) {
        $newGroupAcl = null;
    }

    $product->setProductGroupAcl($newGroupAcl);
    $product->save();
}
unset($product);
unset($products);

/* @var $categories Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Collection */
$categories = Mage::getModel('catalog/category')->getCollection();
$categories->addAttributeToFilter('group_acl', array('neq' => ''));
foreach ($categories as $category) {
    $newGroupAcl = array();
    $groupAcl = explode(',', $category->getGroupAcl());
    foreach ($groupAcl as $groupId) {
        if (!in_array($groupId, $groupIds)) {
            $newGroupAcl[] = $groupId;
        } else {
            $groupName = $groups->getItemById($groupId)->getCustomerGroupCode();
            if (in_array($groupName, $specialTagNames)) {
                $tag = $specialTags->getItemByColumnValue('name', $groupName);
                $tagIds = Mage::helper('unl_customertag')->getTagsByCategory($category)->getAllIds();
                if (!in_array($tag->getId(), $tagIds)) {
                    $tagIds[] = $tag->getId();
                }

                $category->setAccessTagIds($tagIds);
                $tag->getResource()->addCategoryLinks($category);
            }
        }
    }
    if (empty($newGroupAcl)) {
        $newGroupAcl = null;
    }

    $category->setGroupAcl($newGroupAcl);
    $category->save();
}
unset($category);
unset($categories);

foreach ($groups as $group) {
    $group->delete();
}

$installer->endSetup();
