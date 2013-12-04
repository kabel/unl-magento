<?php

class Unl_Core_Helper_Buros extends Mage_Core_Helper_Abstract
{
    const REVIEW_SET_NAME = 'Buros Test Review';

    protected $_testReviewSetId;

    /**
     * Returns if the given product has the Buros Test Review set
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function isProductTestReview($product)
    {
        return $product->getAttributeSetId() == $this->getTestReviewSetId();
    }

    public function getTestReviewSetId()
    {
        if (empty($this->_testReviewSetId)) {
            $config = Mage::getSingleton('catalog/config');
            $this->_testReviewSetId = $config->getAttributeSetId(Mage_Catalog_Model_Product::ENTITY, self::REVIEW_SET_NAME);
        }

        return $this->_testReviewSetId;
    }
}
