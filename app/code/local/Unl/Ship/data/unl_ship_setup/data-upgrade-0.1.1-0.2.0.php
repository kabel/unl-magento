<?php

/* @var $installer Unl_Ship_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/* @var $pkgs Unl_Ship_Model_Resource_Shipment_Package_Collection */
$pkgs = Mage::getModel('unl_ship/shipment_package')->getCollection();

while ($pkg = $pkgs->fetchItem()) {
    $pkg->setLabelImage(base64_decode($pkg->getLabelImage()));

    if ($pkg->getHtmlLabelImage()) {
        $pkg->setHtmlLabelImage(base64_decode($pkg->getHtmlLabelImage()));
    }

    if ($pkg->getInsDoc()) {
        $pkg->setInsDoc(base64_decode($pkg->getInsDoc()));
    }

    if ($pkg->getIntlDoc()) {
        $pkg->setIntlDoc(base64_decode($pkg->getIntlDoc()));
    }

    $pkg->save();
}

$installer->endSetup();
