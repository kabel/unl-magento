<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CUSTOMER_UNL_LDAP_ACTIVE = 'customer/unl_ldap/active';
    const XML_PATH_CUSTOMER_UNL_LDAP_SERVER = 'customer/unl_ldap/server';
    const XML_PATH_CUSTOMER_UNL_LDAP_BASEDN = 'customer/unl_ldap/basedn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDDN = 'customer/unl_ldap/binddn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDPW = 'customer/unl_ldap/bindpw';

    const CUSTOMER_GROUP_TAX_EXEMPT = 'Tax Exempt Org';

    const CUSTOMER_TAG_STUDENT       = 'UNL Student';
    const CUSTOMER_TAG_STUDENT_FEES  = 'UNL Student - Fee Paying';
    const CUSTOMER_TAG_FACULTY_STAFF = 'UNL Faculty/Staff';

    protected $_cache = array();

    protected $_affiliationMap = array(
        self::CUSTOMER_TAG_STUDENT => array(
            'student'
        ),
        self::CUSTOMER_TAG_FACULTY_STAFF => array(
            'faculty',
            'staff',
            'continue services',
            'affiliate',
            'volunteer',
        ),
    );

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

    public function cache($key, $value)
    {
        $this->_cache[$key] = $value;
    }

    public function fetchPfUID($uid)
    {
        if (isset($this->_cache[$uid])) {
            return $this->_cache[$uid];
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_UNL_LDAP_ACTIVE)) {
            UNL_Peoplefinder_Driver_LDAP::$ldapServer = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_SERVER);
            UNL_Peoplefinder_Driver_LDAP::$baseDN = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BASEDN);
            UNL_Peoplefinder_Driver_LDAP::$bindDN = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BINDDN);
            UNL_Peoplefinder_Driver_LDAP::$bindPW = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BINDPW);
            $driver = new UNL_Peoplefinder_Driver_LDAP();
        } else {
            $driver = null;
        }

        do {
            $pf = new UNL_Peoplefinder($driver);
            $retry = false;
            // The Pf Drivers now throw exceptions if a users isn't found
            try {
                $r = $pf->getUID($uid);
                $this->cache($uid, $r);
            } catch (Exception $e) {
                if ($e->getCode() != 404) {
                    Mage::logException($e);
                    if (null !== $driver) {
                        $driver = null;
                        $retry = true;
                    }
                }
                $r = null;
            }
        } while ($retry);

        return $r;
    }

    public function isValidPf($uid)
    {
        return ($this->fetchPfUID($uid) !== null);
    }

    public function isFacultyStaff($uid)
    {
        if ($r = $this->fetchPfUID($uid)) {
            foreach ($r->eduPersonAffiliation as $affil) {
                if (in_array($affil, $this->_affiliationMap[self::CUSTOMER_TAG_FACULTY_STAFF])) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isStudent($uid)
    {
        if ($r = $this->fetchPfUID($uid)) {
            foreach ($r->eduPersonAffiliation as $affil) {
                if (in_array($affil, $this->_affiliationMap[self::CUSTOMER_TAG_STUDENT])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Add peoplefinder data to Varian Object
     *
     * @param $data Varien_Object
     */
    public function loadPfData($data)
    {
        $user = $this->getAuth()->getUser();
        if ($r = $this->fetchPfUID($user)) {

            if (empty($data['email']) && isset($r->mail) && $r->mail->valid()) {
                if (isset($r->unlEmailAlias)) {
                    $data['email'] = $r->unlEmailAlias . '@unl.edu';
                } else {
                    $data['email'] = (string)$r->mail;
                }
            }

            if (empty($data['firstname'])) {
                if (isset($r->eduPersonNickname)) {
                    $data['firstname'] = (string)$r->eduPersonNickname;
                } else {
                    $data['firstname'] = (string)$r->givenName;
                }
            }

            if (empty($data['lastname'])) {
                $data['lastname'] = (string)$r->sn;
            }
        }
    }

    public function getDisplayName($uid = false)
    {
        if ($uid === false) {
            $uid = $this->getAuth()->getUser();
        }

        if ($r = $this->fetchPfUID($uid)) {
            if (isset($r->displayName)) {
                return (string)$r->displayName;
            }
        }
        return $uid;
    }

    /**
     * Assigns customer tags based on peoplefinder results
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $uid
     */
    public function assignCustomerTags($customer, $uid)
    {
        if ($this->isValidPf($uid)) {
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
                $condition = $this->isStudent($uid);
                break;
            case self::CUSTOMER_TAG_FACULTY_STAFF:
                $condition = $this->isFacultyStaff($uid);
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
        $group = Mage::getModel('customer/group')->load(self::CUSTOMER_GROUP_TAX_EXEMPT, 'customer_group_code');
        $this->_assignCustomerGroup($customer, $group);
    }

    public function revokeCostObjectAuth($customer)
    {
        $group = Mage::getModel('customer/group')->load(self::CUSTOMER_GROUP_TAX_EXEMPT, 'customer_group_code');
        if ($customer->getGroupId() == $group->getId()) {
            $group->unsetData()
                ->load(Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $customer->getStoreId()));
            $this->_assignCustomerGroup($customer, $group);
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
    protected function _assignCustomerGroup($customer, $group, $triggerSave = true)
    {
        if ($customer->getGroupId() != $group->getId()) {
            $customer->setGroupId($group->getId());
            if ($triggerSave) {
                $customer->save();
            }
        }
    }

	/**
     * Retieves a collection of the special customer tags
     *
     * @return Unl_CustomerTag_Model_Mysql4_Tag_Collection
     */
    protected function _getSpecialCustomerTagsCollection()
    {
        if (null === $this->_specialTagCollection) {
            /* @var $collection Unl_CustomerTag_Model_Mysql4_Tag_Collection */
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addFieldToFilter('name', array('in' => $this->_specialCustomerTags));
            $this->_specialTagCollection = $collection;
        }

        return $this->_specialTagCollection;
    }
}
