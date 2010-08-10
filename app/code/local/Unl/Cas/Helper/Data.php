<?php

class Unl_Cas_Helper_Data extends Mage_Core_Helper_Abstract
{
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
    
    /**
     * Add peoplefinder data to Varian Object
     *
     * @param $data Varien_Object
     */
    public function loadPfData($data)
    {
        $user = $this->getAuth()->getUser();
        $pf = new UNL_Peoplefinder();
        if ($r = $pf->getUID($user)) {
            if (empty($data['email']) && !empty($r->mail)) {
                if (isset($r->unlEmailAlias)) {
                    $data['email'] = $r->unlEmailAlias . '@unl.edu';
                } else {
                    $data['email'] = $r->mail;
                }
            }
            
            if (empty($data['firstname'])) {
                $data['firstname'] = $r->givenName;
            }
            
            if (empty($data['lastname'])) {
                $data['lastname'] = $r->sn;
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
        $pf = new UNL_Peoplefinder();
        if ($r = $pf->getUID($uid)) {
            $affiliation = $r->eduPersonPrimaryAffiliation;
            $studentModel = Mage::getModel('customer/group')->load('UNL Student', 'customer_group_code');
            $facStaffModel = Mage::getModel('customer/group')->load('UNL Faculty/Staff', 'customer_group_code');
            
            if (strpos($affiliation, 'student') !== false) {
               if ($customer->getGroupId() != $studentModel->getId()) {
                   $customer->setData('group_id', $studentModel->getId());
               } 
            } elseif (strpos($affiliation, 'staff') !== false || strpos($affiliation, 'faculty') !== false) {
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
