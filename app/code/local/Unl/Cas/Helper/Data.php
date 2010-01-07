<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Mage
 * @package    Mage_Customer
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Customer Data Helper
 *
 * @category   Mage
 * @package    Mage_Customer
 * @author     Magento Core Team <core@magentocommerce.com>
 */
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
