<?php

require_once "Mage/Customer/controllers/AccountController.php";

class Unl_Cas_AccountController extends Mage_Customer_AccountController
{
    // BEGIN: Reset parent controller additions

    protected $_cookieCheckActions = array('cas', 'createpost');

    public function preDispatch()
    {
        return Mage_Core_Controller_Front_Action::preDispatch();
    }

    public function postDispatch()
    {
        Mage_Core_Controller_Front_Action::postDispatch();
    }

    protected function _goNowhere()
    {
        $this->_forward('noroute');
    }

    public function indexAction()
    {
        $this->_goNowhere();
    }

    public function confirmAction()
    {
        $this->_goNowhere();
    }

    public function loginAction()
    {
        $this->_forward('cas');
    }

    public function loginPostAction()
    {
        $this->_goNowhere();
    }

    public function confirmationAction()
    {
        $this->_goNowhere();
    }

    public function forgotPasswordAction()
    {
        $this->_goNowhere();
    }

    public function forgotPasswordPostAction()
    {
        $this->_goNowhere();
    }

    public function resetPasswordAction()
    {
        $this->_goNowhere();
    }

    public function resetPasswordPostAction()
    {
        $this->_goNowhere();
    }

    public function editAction()
    {
        $this->_goNowhere();
    }

    public function editPostAction()
    {
        $this->_goNowhere();
    }

    // END

    public function logoutAction()
    {
        $session = $this->_getSession();

        if ($session->isLoggedIn()) {
            $session->logout();
        }

        $this->_getCasAuth()->logout(Mage::getUrl());
    }

    /**
     * Customer login via CAS
     *
     */
    public function casAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account/');
            return;
        }

        $auth = $this->_getCasAuth();
        if ($auth->isLoggedIn()) {
            if ($customer = $this->_checkUidExists($auth->getUser())) {
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                $this->_loginPostRedirect();
                return;
            } else {
                $pfData = new Varien_Object();
                Mage::helper('unl_cas/ldap')->populateLdapData($pfData);

                if (!empty($pfData['email'])) {
                    $this->_createCustomerFromLdapData($pfData);
                    return;
                }
                $this->_redirect('*/*/create', array('_secure' => true));
                return;
            }
        } else {
            $auth->login();
        }
    }

    /**
     * Link an existing customer account to CAS
     *
     */
    public function caslinkAction()
    {
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_redirect('unl_cas/account/cas');
            return;
        }

        $auth = $this->_getCasAuth();
        if ($auth->isLoggedIn()) {
            if ($customer = $this->_checkUidExists($auth->getUser())) {
                if ($customer->getId() != $this->_getSession()->getCustomerId()) {
                    $this->_getSession()->setFailedLink(true);
                    $this->_getSession()->addError(
                        $this->__('Account linking failed. The UNL account %s is already linked with another customer. If you believe this is an error please contact us at <a href="%s">%s</a>. Please logout to try again.',
                            $auth->getUser(),
                            'mailto:' . Mage::getStoreConfig('customer/unl_ldap/error_email'),
                            Mage::getStoreConfig('customer/unl_ldap/error_email')
                        )
                    );
                }
                $this->_redirect('customer/account/');
            } else {
                $customer = $this->_getSession()->getCustomer();
                $customer->setUnlCasUid($auth->getUser());
                $customer->save();
                Mage::helper('unl_cas')->assignCustomerTags($customer, $auth->getUser());
                $this->_redirect('customer/account/');
            }
        } else {
            $auth->login();
        }
    }

    /**
     * Save a session flag for ignoring CAS notice
     *
     */
    public function ignoreLinkAction()
    {
        if (!$this->getRequest()->isPost() || !$this->_getSession()->isLoggedIn()) {
            return;
        }

        $this->_getSession()->setIgnoreCasLink(true);
    }

    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if ($this->getRequest()->getParam('checkout')) {
            $session->setBeforeAuthUrl(Mage::getModel('core/url')->getUrl('checkout/onepage'));
            $this->_redirectUrl($session->getBeforeAuthUrl(true));
            return;
        }

        parent::_loginPostRedirect();
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if (!$this->_checkSessionAndAuth()) {
            return;
        }

        parent::createAction();
    }

    /**
     * Retrieves a customer by CAS user ID
     *
     * @param string $uid
     * @return Mage_Customer_Model_Customer
     */
    protected function _checkUidExists($uid)
    {
        $resource = Mage::getModel('customer/customer')->getResourceCollection();
        $resource->addAttributeToFilter('unl_cas_uid', array('eq' => $uid));
        $resource->load();

        if (count($resource)) {
            return current($resource->getItems());
        } else {
            return false;
        }
    }

    /**
     * Automatically creates a customer or links with an existing customer
     * based on information in $data retrieved from LDAP.
     *
     * @param Varien_Object $data
     */
    protected function _createCustomerFromLdapData(Varien_Object $data)
    {
        $customer = $this->_getCustomer($data);

        try {
            $customer->save();
            $this->_dispatchRegisterSuccess($customer);
            $this->_successProcessRegistration($customer);
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                try {
                    $customer->loadByEmail($data['email']);

                    // prevent another CAS user from taking this account
                    if ($customer->getUnlCasUid()) {
                        throw $e;
                    }

                    $customer->setData('unl_cas_uid', $this->_getCasAuth()->getUser())
                        ->save();

                    $this->_getSession()->setCustomerAsLoggedIn($customer);
                    $this->_loginPostRedirect();
                } catch (Exception $e) {
                    $this->_failException($e);
                }
            } else {
                $this->_failException($e);
            }
        } catch (Exception $e) {
            $this->_failException($e);
        }
    }

    protected function _failException($e)
    {
        $this->_getSession()->addException($e, $this->__('Can\'t save customer'));
        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure'=>true)));
    }

    protected function _checkSessionAndAuth()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('customer/account');
            return false;
        }

        $auth = $this->_getCasAuth();
        if (!$auth->isLoggedIn()) {
            $this->_redirect('*/*/cas', array('_secure' => true));
            return false;
        }

        if ($customer = $this->_checkUidExists($auth->getUser())) {
            $this->_getSession()->setCustomerAsLoggedIn($customer);
            $this->_loginPostRedirect();
            return false;
        }

        return true;
    }

    public function createPostAction()
    {
        /** @var $session Mage_Customer_Model_Session */
        $session = $this->_getSession();
        if (!$this->_checkSessionAndAuth()) {
            return;
        }

        $randomPass = $this->_getCustomer()->getPassword();
        $this->getRequest()->setPost('password', $randomPass);
        $this->getRequest()->setPost('confirmation', $randomPass);

        parent::createPostAction();
    }

    /* Extend
     * @see Mage_Customer_AccountController::_getCustomer()
     * by setting the CAS attriubte for the customer
     */
    protected function _getCustomer(Varien_Object $defaultData = null)
    {
        $customer = parent::_getCustomer();

        if ($defaultData) {
            $customer->addData($defaultData->toArray());
        }

        $customer->setUnlCasUid($this->_getCasAuth()->getUser());

        $randomPass = $customer->generatePassword();
        $customer->setPassword($randomPass);

        return $customer;
    }

    /* Extend
     * @see Mage_Customer_AccountController::_getUrl()
     * by always sending index url to customer account index
     */
    protected function _getUrl($url, $params = array())
    {
        if ($url == '*/*/index') {
            return parent::_getUrl('customer/account/index', $params);
        }

        return parent::_getUrl($url, $params);
    }

    /**
     * Returns the UNL CAS authentication implementation
     *
     * @return UNL_Auth_SimpleCAS
     */
    protected function _getCasAuth()
    {
        return Mage::helper('unl_cas')->getAuth();
    }
}
