<?php

class Unl_Payment_Model_Account extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_payment/account');
    }

    public function getRelatedProductIds()
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getModel('catalog/product')->getCollection();

        $collection->addAttributeToFilter('unl_payment_account', $this->getId());

        return $collection->getAllIds();
    }

    public function addRelations($productIds)
    {
        if (!$this->getId()) {
            return $this;
        }

        $oldProductIds = $this->getRelatedProductIds();

        $insert = array_diff($productIds, $oldProductIds);
        $delete = array_diff($oldProductIds, $productIds);

        /* @var $productUpdate Mage_Catalog_Model_Product_Action */
        $productUpdate = Mage::getSingleton('catalog/product_action');

        if (!empty($insert)) {
            $productUpdate->updateAttributes($insert, array(
                'unl_payment_account' => $this->getId()
            ), 0);
        }

        if (!empty($delete)) {
            $productUpdate->updateAttributes($delete, array(
                'unl_payment_account' => ''
            ), 0);
        }

        return $this;
    }
}
