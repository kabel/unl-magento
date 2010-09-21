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
        
        $auth = Mage::helper('unl_cas')->getAuth();
        if ($auth->isLoggedIn()) {
            if ($customer = $this->_checkUidExists($auth->getUser())) {
                $this->_getSession()->setCustomerAsLoggedIn($customer);
                $this->_loginPostRedirect();
                return;
            } else {
                $pfData = new Varien_Object();
                Mage::helper('unl_cas')->loadPfData($pfData);
                
                if (!empty($pfData['email'])) {
                    $this->_createCustomerFromPfData($pfData);
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
     * Define target URL and redirect customer after logging in
     */
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();

        if (!$session->getBeforeAuthUrl() || $session->getBeforeAuthUrl() == Mage::getBaseUrl() ) {

            // Set default URL to redirect customer to
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());

            // Redirect customer to the last page visited after logging in
            if ($session->isLoggedIn())
            {
                if (!Mage::getStoreConfigFlag('customer/startup/redirect_dashboard')) {
                    if ($referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME)) {
                        $referer = Mage::helper('core')->urlDecode($referer);
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
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
    protected function _createCustomerFromPfData($data)
    {
        /* @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setId(null);
        
        $customer->addData($data->toArray());
        $uid = Mage::helper('unl_cas')->getAuth()->getUser();
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
        
        $auth = Mage::helper('unl_cas')->getAuth();
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
     */
    public function createPostAction()
    {
        if (!$this->_checkSessionAndAuth()) {
            return;
        }
        
        if ($this->getRequest()->isPost()) {
            $errors = array();

            if (!$customer = Mage::registry('current_customer')) {
                $customer = Mage::getModel('customer/customer')->setId(null);
            }

			$pass = $customer->generatePassword();
            $customer->setPassword($pass);
			$customer->setConfirmation($pass);
            
            foreach (Mage::getConfig()->getFieldset('customer_account') as $code=>$node) {
                if ($node->is('create') && ($value = $this->getRequest()->getParam($code)) !== null) {
                    $customer->setData($code, $value);
                }
            }

            if ($this->getRequest()->getParam('is_subscribed', false)) {
                $customer->setIsSubscribed(1);
            }

            $uid = Mage::helper('unl_cas')->getAuth()->getUser();
            $customer->setData('unl_cas_uid', $uid);

            if ($this->getRequest()->getPost('create_address')) {
                $address = Mage::getModel('customer/address')
                    ->setData($this->getRequest()->getPost())
                    ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                    ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false))
                    ->setId(null);
                $customer->addAddress($address);

                $errors = $address->validate();
                if (!is_array($errors)) {
                    $errors = array();
                }
            }

            try {
                $validationCustomer = $customer->validate();
                if (is_array($validationCustomer)) {
                    $errors = array_merge($validationCustomer, $errors);
                }
                $validationResult = count($errors) == 0;

                if (true === $validationResult) {
                    $this->_completeCustomer($customer);
                    return;
                } else {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                    if (is_array($errors)) {
                        foreach ($errors as $errorMessage) {
                            $this->_getSession()->addError($errorMessage);
                        }
                    }
                    else {
                        $this->_getSession()->addError($this->__('Invalid customer data'));
                    }
                }
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage())
                    ->setCustomerFormData($this->getRequest()->getPost());
            }
            catch (Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                    ->addException($e, $this->__('Can\'t save customer'));
            }
        }
        /**
         * Protect XSS injection in user input
         */
        $this->_getSession()->setEscapeMessages(true);
        $this->_redirectError(Mage::getUrl('*/*/create', array('_secure'=>true)));
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
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_getSession()->addSuccess($this->__('Thank you for registering with %s', Mage::app()->getStore()->getName()));

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
}
