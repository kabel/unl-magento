<?php

class Unl_Cas_Model_Auth
{
    /**
     * Use CAS authentication for login
     *
     * @return Mage_Admin_Model_User|null
     */
    public function loginCas()
    {
        $auth = Mage::helper('unl_cas')->getAuth();
        if ($auth->isLoggedIn()) {
            $username = $auth->getUser();

            // simulate normal authentication
            try {
                /* @var $user Mage_Admin_Model_User */
                $user = Mage::getModel('admin/user');
                $user->loadByUsername($username);

                if ($user->getId()) {
                    $this->_simulateLogin($user)
                        ->_simulateSessionLogin($user);
                } else {
                    Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                Mage::dispatchEvent('admin_session_user_login_failed', array('user_name'=>$username, 'exception' => $e));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        } else {
            $auth->login();
            return;
        }

        return $user;
    }

    /**
     * Use LDAP authentication for login given a password
     *
     * @param Mage_Admin_Model_User $user
     * @param string $password
     */
    public function loginLdap($user, $password)
    {
        $auth = Mage::helper('unl_cas/ldap');
        $username = $user->getUsername();
        try {
            $auth->authenticate($user, $password);
            if ($user->getId()) {
                $this->_simulateLogin($user)
                    ->_simulateSessionLogin($user);
            } else {
                Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
            }
        }
        catch (Mage_Core_Exception $e) {
            Mage::dispatchEvent('admin_session_user_login_failed', array('user_name'=>$username, 'exception' => $e));
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    /**
     * Do the same authenticated user checks found in
     * @see Mage_Admin_Model_User::login()
     * without any authentication events and assuming authentication
     * was already successful.
     *
     * @param Mage_Admin_Model_User $user
     * @throws Mage_Core_Exception
     * @return Unl_Cas_Model_Auth
     */
    protected function _simulateLogin($user)
    {
        // assumes authentication was successful
        try {
            if (!$user->getIsCas()) {
                Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
            }
            if ($user->getIsActive() != '1') {
                Mage::throwException(Mage::helper('adminhtml')->__('This account is inactive.'));
            }
            if (!$user->hasAssigned2Role($user->getId())) {
                Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
            }
        } catch (Mage_Core_Exception $e) {
            $user->unsetData();
            throw $e;
        }

        $user->getResource()->recordLogin($user);

        return $this;
    }

    /**
     * Do the same successful session login actions found in
     * @see Mage_Admin_Model_Session::login()
     *
     * @param Mage_Admin_Model_User $user
     * @return Unl_Cas_Model_Auth
     */
    protected function _simulateSessionLogin($user)
    {
        /* @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('admin/session');
        $session->renewSession();

        if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
            Mage::getSingleton('adminhtml/url')->renewSecretUrls();
        }

        $session->setIsFirstPageAfterLogin(true);
        $session->setUser($user);
        $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));

        return $this;
    }
}
