<?php

class Unl_Cas_Adminhtml_CasController extends Mage_Adminhtml_Controller_Action
{
    public function preDispatch()
    {
        if ($this->getRequest()->getActionName() == 'logincas') {
            //prevent the predispatch events so we don't need login logic
            $this->setFlag('', self::FLAG_NO_PRE_DISPATCH, true);
        }

        return parent::preDispatch();
    }

    /**
     * Get the admin session singleton instance
     *
     * @return Mage_Admin_Model_Session
     */
    protected function _getAdminSession()
    {
        return Mage::getSingleton('admin/session');
    }

    public function logincasAction()
    {
        $session = $this->_getAdminSession();

        if ($session->isLoggedIn()) {
            $this->_redirectLogin();
            return;
        }

        $referer = $this->getRequest()->getServer('HTTP_REFERER');
        $baseUrl = $this->getUrl();
        if (strpos($referer, 'login') === false && strpos($referer, $baseUrl) === 0) {
            $session->setBeforeCasUrl(substr($referer, strlen($baseUrl)));
        }

        if (Mage::getSingleton('unl_cas/auth')->loginCas()) {
            $this->_redirectLogin();
        }
    }

    protected function _redirectLogin()
    {
        $session = $this->_getAdminSession();
        if ($url = $session->getBeforeCasUrl(true)) {
            $this->_redirect($url);
        } else {
            $this->_redirect('adminhtml');
        }
    }
}
