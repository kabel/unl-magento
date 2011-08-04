<?php

class Unl_Core_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'logincas') {
            //prevent the predispatch events so we don't need login logic
            $this->setFlag('', self::FLAG_NO_PRE_DISPATCH, true);
        }

        return parent::preDispatch();
    }

    public function logincasAction()
    {
        /* @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton('admin/session');

        $referer = $this->getRequest()->getServer('HTTP_REFERER');
        $baseUrl = $this->getUrl();
        if (strpos($referer, 'login') === false && strpos($referer, $baseUrl) === 0) {
            $session->setBeforeCasUrl(substr($referer, strlen($baseUrl)));
        }

        if ($session->isLoggedIn()) {
            $this->_redirectLogin();
            return;
        }

        $auth = Mage::helper('unl_cas')->getAuth();
        if ($auth->isLoggedIn()) {
            $username = $auth->getUser();

            // simulate normal authentication
            try {
                /* @var $user Mage_Admin_Model_User */
                $user = Mage::getModel('admin/user');
                // logic to fetch and check user
                $user->loadByUsername($username);
                if ($user->getId()) {
                    if (!$user->getIsCas()) {
                        Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
                    }
                    if ($user->getIsActive() != '1') {
                        Mage::throwException(Mage::helper('adminhtml')->__('This account is inactive.'));
                    }
                    if (!$user->hasAssigned2Role($user->getId())) {
                        Mage::throwException(Mage::helper('adminhtml')->__('Access denied.'));
                    }

                    $user->getResource()->recordLogin($user);

                    if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                        Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                    }
                    $session->setIsFirstPageAfterLogin(true);
                    $session->setUser($user);
                    $session->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                    Mage::dispatchEvent('admin_session_user_login_success', array('user'=>$user));
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
        }

        $this->_redirectLogin();
    }

    protected function _redirectLogin()
    {
        $session = Mage::getSingleton('admin/session');
        if ($url = $session->getBeforeCasUrl(true)) {
            $this->_redirect($url);
        } else {
            $this->_redirect('adminhtml');
        }


    }
}
