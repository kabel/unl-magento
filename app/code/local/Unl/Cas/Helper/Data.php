<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_cache = array();
    
    protected $_specialCustomerGroups = array(
        'UNL Student',
        'UNL Student - Fee Paying',
        'UNL Faculty/Staff',
        'UNL Cost Object Authorized'
    );
    protected $_specialGroupsCollection;
    
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
        if ($this->_cache[$uid] !== null) {
            return $this->_cache[$uid];
        }
        
        $pf = new UNL_Peoplefinder();
        if ($r = $pf->getUID($uid)) {
            $this->cache($uid, $r);
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
                $data['firstname'] = (string)$r->givenName;
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
     * Sets the customer's group id and saves, if needed
     * 
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Customer_Model_Group $group
     */
    protected function _assignCustomerGroup($customer, $group)
    {
        if ($customer->getGroupId() != $group->getId()) {
            $customer->setGroupId($group->getId());
            $customer->save();
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
