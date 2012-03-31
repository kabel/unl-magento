<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CUSTOMER_TAG_STUDENT       = 'UNL Student';
    const CUSTOMER_TAG_STUDENT_FEES  = 'UNL Student - Fee Paying';
    const CUSTOMER_TAG_FACULTY_STAFF = 'UNL Faculty/Staff';

    protected $_specialCustomerTags = array(
        self::CUSTOMER_TAG_STUDENT,
        self::CUSTOMER_TAG_STUDENT_FEES,
        self::CUSTOMER_TAG_FACULTY_STAFF,
    );
    protected $_specialTagCollection;

    /**
     * Check customer is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Checks if the currently logged in customer is a CAS user
     *
     * @return boolean
     */
    public function isCustomerCasUser()
    {
        if ($this->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();
            return $customer->getUnlCasUid() != '';
        }

        return false;
    }

    public function canShowCasLinkNotice()
    {
        if (!$this->isCustomerCasUser()) {
            $session = Mage::getSingleton('customer/session');
            return $session->getFailedLink() !== true && $session->getIgnoreCasLink() !== true;
        }

        return false;
    }

    /**
     * Get UNL_Auth object
     *
     * @return UNL_Auth_SimpleCAS
     */
    public function getAuth()
    {
        return UNL_Auth::factory('SimpleCAS', array('requestClass' => 'Zend_Http_Client'));
    }

    public function getCasUrl()
    {
        return $this->_getUrl('unlcas/account/cas');
    }

    public function getRegisterPostUrl()
    {
        return $this->_getUrl('unlcas/account/createpost');
    }

    /**
     * Assigns customer tags based on peoplefinder results
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $uid
     */
    public function assignCustomerTags($customer, $uid)
    {
        if (Mage::helper('unl_cas/ldap')->isInLdap($uid)) {
            $tagIds = Mage::helper('unl_customertag')->getTagIdsByCustomer($customer);
            $initCount = count($tagIds);
            $doSave = false;

            foreach ($this->_specialCustomerTags as $tagName) {
                $tagIds = $this->_adjustCustomersTags($uid, $tagName, $tagIds);
                if (!$doSave && count($tagIds) != $initCount) {
                    $doSave = true;
                }
            }

            if ($doSave) {
                $customer->setAddedCustomerTagIds($tagIds);
                Mage::getResourceModel('unl_customertag/tag')->addTagLinks($customer);
            }
        } else {
            $this->revokeSpecialCustomerTags($customer);
        }
    }

    protected function _adjustCustomersTags($uid, $tagName, $tagIds)
    {
        $specialTags = $this->_getSpecialCustomerTagsCollection();
        $tag = $specialTags->getItemByColumnValue('name', $tagName);
        if (!$tag) {
            return $tagIds;
        }
        $i = array_search($tag->getId(), $tagIds);

        switch ($tagName) {
            case self::CUSTOMER_TAG_STUDENT:
                $condition = Mage::helper('unl_cas/ldap')->isStudent($uid);
                break;
            case self::CUSTOMER_TAG_FACULTY_STAFF:
                $condition = Mage::helper('unl_cas/ldap')->isFacultyStaff($uid);
                break;
            case self::CUSTOMER_TAG_STUDENT_FEES:
                //TODO: Needs logic for fees data store
                //break;
            default:
                $condition = false;
        }

        if ($condition) {
            if ($i === false) {
                $tagIds[] = $tag->getId();
            }
        } elseif ($i !== false) {
            unset($tagIds[$i]);
        }

        return $tagIds;
    }

    /**
     * Removes all special tags
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function revokeSpecialCustomerTags($customer)
    {
        $tagIds = Mage::helper('unl_customertag')->getTagIdsByCustomer($customer);
        $doSave = false;

        foreach ($this->_getSpecialCustomerTagsCollection() as $tag) {
            $i = array_search($tag->getId(), $tagIds);
            if ($i !== false) {
                $doSave = true;
                unset($tagIds[$i]);
            }
        }

        if ($doSave) {
            $customer->setAddedCustomerTagIds($tagIds);
            Mage::getResourceModel('unl_customertag/tag')->addTagLinks($customer);
        }
    }

    /**
     * Assigns the customer group to the special group for Cost Object orders
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function authorizeCostObject($customer)
    {
        $group = Mage::getModel('customer/group')->load(Unl_Core_Helper_Data::TAX_GROUP_EXEMPT_ORG, 'customer_group_code');
        $this->_assignCustomerGroup($customer, $group);
    }

    public function revokeCostObjectAuth($customer)
    {
        if ($customer->getPreviousGroupId()) {
            $group = Mage::getModel('customer/group')->load(Unl_Core_Helper_Data::TAX_GROUP_EXEMPT_ORG, 'customer_group_code');
            if ($customer->getGroupId() == $group->getId()) {
                $group->unsetData()
                    ->load($customer->getPreviousGroupId());

                if (!$group->getId()) {
                    $group->unsetData()
                        ->load(Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $customer->getStoreId()));
                }

                $this->_assignCustomerGroup($customer, $group, false);
            }
        }
    }

    public function isCustomerCostObjectAuthorized($customer)
    {
        foreach (Mage::helper('unl_customertag')->getTagsByCustomer($customer) as $tag) {
            if ($tag->getName() == self::CUSTOMER_TAG_FACULTY_STAFF) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sets the customer's group id and saves, if needed
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Customer_Model_Group $group
     */
    protected function _assignCustomerGroup($customer, $group, $savePrevious = true, $triggerSave = true)
    {
        if ($customer->getGroupId() != $group->getId()) {
            if ($savePrevious) {
                $customer->setPreviousGroupId($customer->getGroupId());
            } else {
                $customer->setPreviousGroupId(null);
            }
            $customer->setGroupId($group->getId());
            if ($triggerSave) {
                $customer->save();
            }
        }
    }

	/**
     * Retieves a collection of the special customer tags
     *
     * @return Unl_CustomerTag_Model_Resource_Tag_Collection
     */
    protected function _getSpecialCustomerTagsCollection()
    {
        if (null === $this->_specialTagCollection) {
            /* @var $collection Unl_CustomerTag_Model_Resource_Tag_Collection */
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addFieldToFilter('name', array('in' => $this->_specialCustomerTags));
            $this->_specialTagCollection = $collection;
        }

        return $this->_specialTagCollection;
    }
}
