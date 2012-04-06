<?php

require_once "Mage/Adminhtml/controllers/IndexController.php";

class Unl_Cas_Adminhtml_IndexController extends Mage_Adminhtml_IndexController
{
    /* Extends
     * @see Mage_Adminhtml_IndexController::_outTemplate()
     * by also init'ing admin/session message
     */
    protected function _outTemplate($tplName, $data = array())
    {
        $this->_initLayoutMessages('admin/session');
        parent::_outTemplate($tplName, $data);
    }

    /* Overrides
     * @see Mage_Adminhtml_IndexController::logoutAction()
     * by using custom session logic
     */
    public function logoutAction()
    {
        /* @var $adminSession Mage_Admin_Model_Session */
        $adminSession = Mage::getSingleton('admin/session');
        $user = $adminSession->getUser();
        $isCAS = $user ? $user->getIsCas() : false;
        $adminSession->unsetAll();
        $adminSession->renewSession();
        $adminSession->addSuccess(Mage::helper('adminhtml')->__('You have logged out.'));
        if ($isCAS) {
            $auth = Mage::helper('unl_cas')->getAuth();
            $adminSession->addSuccess($this->__(
                'If you are finished with all UNL services, please remember to <a href="%s">logout of your UNL session</a>.',
                $auth->getProtocol()->getLogoutUrl($this->getUrl('*'))
            ));
        }

        $this->_redirect('*');
    }

    /* Overrides
     * @see Mage_Adminhtml_IndexController::forgotpasswordAction()
     * by giving a message to CAS users.
     */
    public function forgotpasswordAction()
    {
        $email = (string) $this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->_getSession()->addError($this->__('Invalid email address.'));
                $this->_outTemplate('forgotpassword');
                return;
            }
            $collection = Mage::getResourceModel('admin/user_collection');
            /** @var $collection Mage_Admin_Model_Mysql4_User_Collection */
            $collection->addFieldToFilter('email', $email);
            $collection->load(false);

            if ($collection->getSize() > 0) {
                foreach ($collection as $item) {
                    $user = Mage::getModel('admin/user')->load($item->getId());
                    if ($user->getId()) {

                        if ($user->getIsCas()) {
                            $this->_getSession()->addNotice(
                                Mage::helper('unl_cas')->__('If you are a UNL user, please use the <a href="%s">UNL ID managment service</a> to reset your password.', Mage::getStoreConfig('admin/unl_ldap/idm_server')));
                            $this->_redirect('*/*/login');
                            return;
                        }

                        $newResetPasswordLinkToken = Mage::helper('admin')->generateResetPasswordLinkToken();
                        $user->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                        $user->save();
                        $user->sendPasswordResetConfirmationEmail();
                    }
                    break;
                }
            }
            $this->_getSession()
                ->addSuccess(Mage::helper('adminhtml')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('adminhtml')->htmlEscape($email)));
            $this->_redirect('*/*/login');
            return;
        } elseif (!empty($params)) {
            $this->_getSession()->addError(Mage::helper('adminhtml')->__('The email address is empty.'));
        }

        $data = array(
            'email' => $email
        );
        $this->_outTemplate('forgotpassword', $data);
    }
}
