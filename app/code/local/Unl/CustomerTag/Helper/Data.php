<?php

class Unl_CustomerTag_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Retireve a customer tag collection and store it in the customer model
     *
     * @param Mage_Customer_Model_Customer $customer
     * @return Unl_CustomerTag_Model_Mysql4_Tag_Collection
     */
    public function getTagsByCustomer($customer)
    {
        if (null === $customer->getCustomerTags()) {
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addCustomerFilter($customer->getId());
            $customer->setCustomerTags($collection);
        }

        return $customer->getCustomerTags();
    }

    public function getTagIdsByCustomer($customer)
    {
        $tags = $this->getTagsByCustomer($customer);
        return $tags->getAllIds();
    }

    public function getTagsByProduct($product)
    {
        if (null === $product->getCustomerTags()) {
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addProductFilter($product->getId());
            $product->setCustomerTags($collection);
        }

        return $product->getCustomerTags();
    }

    public function getTagsByCategory($category)
    {
        if (null === $category->getCustomerTags()) {
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addCategoryFilter($category->getId());
            $category->setCustomerTags($collection);
        }

        return $category->getCustomerTags();
    }
}
