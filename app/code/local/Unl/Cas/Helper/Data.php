<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_cache = array();
    
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
            $studentModel = Mage::getModel('customer/group')->load('UNL Student', 'customer_group_code');
            $facStaffModel = Mage::getModel('customer/group')->load('UNL Faculty/Staff', 'customer_group_code');
            
            if ($this->isStudent($uid)) {
               if ($customer->getGroupId() != $studentModel->getId()) {
                   $customer->setData('group_id', $studentModel->getId());
               } 
            } elseif ($this->isFacultyStaff($uid)) {
                if ($customer->getGroupId() != $facStaffModel->getId()) {
                   $customer->setData('group_id', $facStaffModel->getId());
               }
            } else {
                $storeId = $customer->getStoreId() ? $customer->getStoreId() : Mage::app()->getStore()->getId();
                $customer->setData('group_id', Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId));
            }
        } else {
            $groupId = $customer->getGroupId();
            if ($groupId == $studentModel->getId() || $groupId == $facStaffModel->getId()) {
                $storeId = $customer->getStoreId() ? $customer->getStoreId() : Mage::app()->getStore()->getId();
                $customer->setData('group_id', Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID, $storeId));
            }
        }
    }
}
