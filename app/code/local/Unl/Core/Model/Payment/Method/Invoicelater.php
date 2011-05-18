<?php

class Unl_Core_Model_Payment_Method_Invoicelater extends Mage_Payment_Model_Method_Abstract
{
    protected $_specialCustomerTags = array(
        'Allow Invoicing',
    );
    protected $_specialTagsCollection;

    /**
     * Payment code name
     *
     * @var string
     */
    protected $_code = 'invoicelater';

    public function getAllowForcePay()
    {
        return true;
    }

    /**
     * Check whether method is available
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (parent::isAvailable($quote)) {
            if (!empty($quote) && $quote->getCustomer()->getId()) {
                $tagIds = Mage::helper('unl_customertag')->getTagIdsByCustomer($quote->getCustomer());
                foreach ($this->_getSpecialCustomerTagsCollection() as $tag) {
                    if (in_array($tag->getId(), $tagIds)) {
                        return true;
                    }
                }
            }

        }

        return false;
    }

    /**
     * Retieves a collection of the special customer tags
     *
     * @return Unl_CustomerTag_Model_Mysql4_Tag_Collection
     */
    protected function _getSpecialCustomerTagsCollection()
    {
        if (null === $this->_specialTagsCollection) {
            /* @var $collection Mage_Customer_Model_Entity_Group_Collection */
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addFieldToFilter('name', array('in' => $this->_specialCustomerTags));
            $this->_specialTagsCollection = $collection;
        }

        return $this->_specialTagsCollection;
    }
}
