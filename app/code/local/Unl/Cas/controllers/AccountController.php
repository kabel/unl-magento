<?php

class Unl_Cas_AccountController extends Mage_Core_Controller_Front_Action
{
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Customer login form page
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

    public function ignoreLinkAction()
    {
        if (!$this->getRequest()->isPost() || !$this->_getSession()->isLoggedIn()) {
            return;
        }

        $this->_getSession()->setIgnoreCasLink(true);
    }

    /**
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if ($this->getRequest()->getParam('checkout')) {
            $session->setBeforeAuthUrl(Mage::getModel('core/url')->getUrl('checkout/onepage'));
        } elseif (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl()) {

            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = Mage::helper('core')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else if ($session->getAfterAuthUrl()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } elseif ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
        } else {
            if (!$session->getAfterAuthUrl()) {
                $session->setAfterAuthUrl($session->getBeforeAuthUrl());
            }
            if ($session->isLoggedIn()) {
                $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }

    /**
     * Customer register form page
     */
    public function createAction()
    {
        if (!$this->_checkSessionAndAuth()) {
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

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
     *
     * @param Varien_Object $data
     */
    protected function _createCustomerFromLdapData($data)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setId(null);

        $customer->addData($data->toArray());
        $uid = $this->_getCasAuth()->getUser();
        $customer->setData('unl_cas_uid', $uid);
        $customer->setPassword($customer->generatePassword());

        try {
            $this->_completeCustomer($customer);
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                try {
                    $customer->loadByEmail($data['email']);
                    $customer->setData('unl_cas_uid', $uid)
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

    /**
     * Create customer account action
     *
     * @see Mage_Customer_AccountController
     */
    public function createPostAction()
    {
        $session = $this->_getSession();
        if (!$this->_checkSessionAndAuth()) {
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

            /* @var $customerForm Mage_Customer_Model_Form */
            $customerForm = Mage::getModel('customer/form');
            $customerForm->setFormCode('customer_account_create')
                ->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            /**
             * Initialize customer group id
             */
            $customer->getGroupId();

            $uid = $this->_getCasAuth()->getUser();
            $customer->setData('unl_cas_uid', $uid);

            if ($this->getRequest()->getPost('create_address')) {
                /* @var $address Mage_Customer_Model_Address */
                $address = Mage::getModel('customer/address');
                /* @var $addressForm Mage_Customer_Model_Form */
                $addressForm = Mage::getModel('customer/form');
                $addressForm->setFormCode('customer_register_address')
                    ->setEntity($address);

                $addressData    = $addressForm->extractData($this->getRequest(), 'address', false);
                $addressErrors  = $addressForm->validateData($addressData);
                if ($addressErrors === true) {
                    $address->setId(null)
                        ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                        ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                    $addressForm->compactData($addressData);
                    $customer->addAddress($address);

                    $addressErrors = $address->validate();
                    if (is_array($addressErrors)) {
                        $errors = array_merge($errors, $addressErrors);
                    }
                } else {
                    $errors = array_merge($errors, $addressErrors);
                }
            }

            try {
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $pass = $customer->generatePassword();
                    $customer->setPassword($pass);
                    $customer->setConfirmation($pass);
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($customerErrors, $errors);
                    }
                }

                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $this->_completeCustomer($customer);
                    return;
                } else {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $session->addError($errorMessage);
                        }
                    } else {
                        $session->addError($this->__('Invalid customer data'));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost());
                if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                    $url = Mage::getUrl('customer/account/forgotpassword');
                    $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                    $session->setEscapeMessages(false);
                } else {
                    $message = $e->getMessage();
                }
                $session->addError($message);
            } catch (Exception $e) {
                $session->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
    }

    /**
     * Saves the customer to the database and redirects
     *
     * @param Mage_Customer_Model_Customer $customer
     */
    protected function _completeCustomer($customer)
    {
        $customer->save();

        if ($customer->isConfirmationRequired()) {
            $customer->sendNewAccountEmail('confirmation', $this->_getSession()->getBeforeAuthUrl());
            $this->_getSession()->addSuccess($this->__('Account confirmation is required. Please, check your e-mail for confirmation link. To resend confirmation email please <a href="%s">click here</a>.',
                Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())
            ));
            $this->_redirectSuccess(Mage::getUrl('customer/account/index', array('_secure'=>true)));
        } else {
            $this->_getSession()->setCustomerAsLoggedIn($customer);
            $url = $this->_welcomeCustomer($customer);
            $this->_redirectSuccess($url);
        }
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @see Mage_Customer_AccountController
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess(
            $this->__('Thank you for registering with %s.', Mage::app()->getStore()->getFrontendName())
        );

        $customer->sendNewAccountEmail($isJustConfirmed ? 'confirmed' : 'registered');

        return $this->_getSuccessUrl();
    }

    protected function _getSuccessUrl()
    {
        $successUrl = Mage::getUrl('customer/account/index', array('_secure'=>true));
        if ($this->_getSession()->getBeforeAuthUrl()) {
            $successUrl = $this->_getSession()->getBeforeAuthUrl(true);
        }
        return $successUrl;
    }

    protected function _getCasAuth()
    {
        return Mage::helper('unl_cas')->getAuth();
    }
}
