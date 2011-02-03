<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CUSTOMER_UNL_LDAP_ACTIVE = 'customer/unl_ldap/active';
    const XML_PATH_CUSTOMER_UNL_LDAP_SERVER = 'customer/unl_ldap/server';
    const XML_PATH_CUSTOMER_UNL_LDAP_BASEDN = 'customer/unl_ldap/basedn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDDN = 'customer/unl_ldap/binddn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDPW = 'customer/unl_ldap/bindpw';

    protected $_cache = array();

    protected $_specialCustomerGroups = array(
        'UNL Student',
        'UNL Student - Fee Paying',
        'UNL Faculty/Staff',
        'UNL Cost Object Authorized'
    );
    protected $_specialGroupsCollection;

    protected $_affiliations = array(
        'UNL Student' => 1,
        'UNL Faculty/Staff' => 2
    );

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

        $pf = new UNL_Peoplefinder($driver);
        // The Pf Drivers now throw exceptions if a users isn't found
        try {
            $r = $pf->getUID($uid);
            $this->cache($uid, $r);
        } catch (Exception $e) {
            if ($e->getCode() != 404) {
                Mage::logException($e);
            }
            $r = null;
        }

        return $r;
    }

    public function isValidPf($uid)
    {
        return ($this->fetchPfUID($uid) !== null);
    }

    public function isFacultyStaff($uid)
    {
        if ($r = $this->fetchPfUID($uid)) {
            $affiliation = $r->eduPersonPrimaryAffiliation;
            return (strpos($affiliation, 'staff') !== false || strpos($affiliation, 'faculty') !== false);
        }

        return false;
    }

    public function isStudent($uid)
    {
        if ($r = $this->fetchPfUID($uid)) {
            $affiliation = $r->eduPersonPrimaryAffiliation;
            return (strpos($affiliation, 'student') !== false);
        }

        return false;
    }

    public function isAllowedAffiliationSwitch($customer, $reload = false)
    {
        if ($reload) {
            $_customer = Mage::getModel('customer/customer')->load($customer->getId());
        } else {
            $_customer = $customer;
        }

        if ($uid = $_customer->getUnlCasUid()) {
            if ($r = $this->fetchPfUID($uid)) {
                $affiliations = 0;
                foreach ($r->eduPersonAffiliation as $aff) {
                    if (strpos($aff, 'student') !== false) {
                        $affiliations |= 1;
                    } elseif (strpos($aff, 'staff') !== false || strpos($aff, 'faculty') !== false) {
                        $affiliations |= 2;
                    }
                }

                if (($affiliations & 3) == 3) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getAffiliations()
    {
        return $this->_affiliations;
    }

    public function getCustomerAffiliation($customer)
    {
        foreach ($this->_getSpecialCustomerGroupsCollection() as $group) {
            if ($customer->getGroupId() == $group->getId()) {
                switch ($group->getCustomerGroupCode()) {
                    case 'UNL Student':
                    case 'UNL Student - Fee Paying':
                        return $this->_affiliations['UNL Student'];
                        break;
                    case 'UNL Faculty/Staff':
                    case 'UNL Cost Object Authorized':
                        return $this->_affiliations['UNL Faculty/Staff'];
                        break;
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

    /**
     * Assigns a group id based on peoplefinder results
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param string $uid
     */
    public function assignGroupId($customer, $uid)
    {
        if ($this->isValidPf($uid)) {
            $specialGroups = $this->_getSpecialCustomerGroupsCollection();
            if ($this->isStudent($uid)) {
                //TODO: Add logic for checking/assigning Fee Paying status
                $this->_assignCustomerGroup($customer, $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Student'));
            } elseif ($this->isFacultyStaff($uid)) {
                $this->_assignCustomerGroup($customer, $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Faculty/Staff'));
            } else {
                $this->revokeSpecialCustomerGroup($customer);
            }
        } else {
            $this->revokeSpecialCustomerGroup($customer);
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

    public function switchAffiliation($customer)
    {
        if (!$this->isAllowedAffiliationSwitch($customer, true) || !Mage::app()->getRequest()->getPost('affiliation_switch')) {
            return;
        }

        $specialGroups = $this->_getSpecialCustomerGroupsCollection();
        //should not trigger save becuase this should only be called on _beforeSave
        switch (Mage::app()->getRequest()->getPost('affiliation_switch')) {
            case $this->_affiliations['UNL Student']:
                //TODO: Add logic for checking/assigning Fee Paying status
                $this->_assignCustomerGroup($customer, $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Student'), false);
                break;
            case $this->_affiliations['UNL Faculty/Staff']:
                $this->_assignCustomerGroup($customer, $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Faculty/Staff'), false);
                break;
        }
    }

    /**
     * Reverts a customer's group classification back to the default
     * if it is a special group
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function revokeSpecialCustomerGroup($customer)
    {
        foreach ($this->_getSpecialCustomerGroupsCollection() as $group) {
            if ($customer->getGroupId() == $group->getId()) {
                $storeId = $customer->getStoreId() ? $customer->getStoreId() : Mage::app()->getStore()->getId();
                $customer->setGroupId(Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId));
                $customer->save();
                break;
            }
        }
    }

    /**
     * Assigns the customer group to the special group for Cost Object orders
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    public function authorizeCostObject($customer)
    {
        $coGroup = $this->_getSpecialCustomerGroupsCollection()->getItemByColumnValue('customer_group_code', 'UNL Cost Object Authorized');
        $this->_assignCustomerGroup($customer, $coGroup);
    }

    public function revokeCostObjectAuth($customer)
    {
        $specialGroups = $this->_getSpecialCustomerGroupsCollection();
        if ($customer->getGroupId() == $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Cost Object Authorized')->getId()) {
            $this->_assignCustomerGroup($customer, $specialGroups->getItemByColumnValue('customer_group_code', 'UNL Faculty/Staff'));
        }
    }

    public function isCustomerCostObjectAuthorized($customer)
    {
        foreach ($this->_getSpecialCustomerGroupsCollection() as $group) {
            if ($customer->getGroupId() == $group->getId()) {
                switch ($group->getCustomerGroupCode()) {
                    case 'UNL Faculty/Staff':
                    case 'UNL Cost Object Authorized':
                        return true;
                        break;
                }
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
