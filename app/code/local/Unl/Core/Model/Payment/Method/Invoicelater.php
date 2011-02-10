<?php

class Unl_Core_Model_Payment_Method_Invoicelater extends Mage_Payment_Model_Method_Abstract
{
    protected $_specialCustomerGroups = array(
        'Allow Invoicing',
    	'Allow Invoicing - Exempt Org'
    );
    protected $_specialGroupsCollection;

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
            if (!empty($quote)) {
                $customer = $quote->getCustomer();
                if ($store = Mage::helper('unl_core')->getSingleStoreFromQuote($quote)) {
                    foreach ($this->_getSpecialCustomerGroupsCollection() as $group) {
                        if ($customer->getGroupId() == $group->getId()) {
                            return true;
                        }
                    }
                }
            }

        }

        return false;
    }

    /**
     * Retieves a collection of the special customer groups
     *
     * @return Mage_Customer_Model_Entity_Group_Collection
     */
    protected function _getSpecialCustomerGroupsCollection()
    {
        if (null === $this->_specialGroupsCollection) {
            /* @var $collection Mage_Customer_Model_Entity_Group_Collection */
            $collection = Mage::getModel('customer/group')->getCollection();
            $collection->addFieldToFilter('customer_group_code', array('in' => $this->_specialCustomerGroups));
            $this->_specialGroupsCollection = $collection;
        }

        return $this->_specialGroupsCollection;
    }
}
