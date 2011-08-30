<?php

class Unl_Cas_Helper_Rss extends Mage_Rss_Helper_Data
{
    /**
     * Authenticate customer on frontend
     *
     */
    public function authFrontend()
    {
        $session = Mage::getSingleton('rss/session');
        if ($session->isCustomerLoggedIn()) {
            return;
        }
        $customerSession = Mage::getSingleton('customer/session');
        if ($customerSession->isLoggedIn()) {
            $session->setCustomer($customerSession->getCustomer());
            return;
        }
        list($username, $password) = $this->authValidate();
        $customer = Mage::getModel('customer/customer')->authenticate($username, $password);
        if ($customer && $customer->getId()) {
            $session->setCustomer($customer);
        } else {
            $this->authFailed();
        }
    }

    /**
     * Authenticate admin and check ACL
     *
     * @param string $path
     */
    public function authAdmin($path)
    {
        $session = Mage::getSingleton('rss/session');
        $adminSession = Mage::getSingleton('admin/session');
        if ($session->isAdminLoggedIn()) {
            if (!$adminSession->isAllowed($path)) {
                $this->authFailed();
            }
            return;
        }

        list($username, $password) = $this->authValidate();
        Mage::getSingleton('adminhtml/url')
            ->setNoSecret(true)
            ->setStore(Mage_Core_Model_Store::ADMIN_CODE);

        try {
            $user = Mage::getModel('admin/user')->loadByUsername($username);
            if ($user->getId() && $user->getIsActive() == '1') {
                if ($user->getIsCas()) {
                    Mage::getSingleton('unl_cas/auth')->loginLdap($user, $password);
                } else {
                    $user = $adminSession->login($username, $password);
                }

                if ($adminSession->isAllowed($path)) {
                    $session->setAdmin($user);
                } else {
                    Mage::throwException($this->__('Access Denied'));
                }
            } else {
                Mage::throwException($this->__('Access Denied'));
            }
        } catch (Mage_Core_Exception $e) {
            $this->authFailed();
            return;
        }
    }
}
