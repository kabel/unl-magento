<?php

/**
 * MOST OF THIS LOGIC HAS BEEN COPIED FROM ITS PARENT TO GET AROUND
 * PRIVATE VISABILITY RESTRICTIONS
 * #######################################
 *
 */
class Unl_Core_Block_Adminhtml_Notification_Security extends Mage_Adminhtml_Block_Notification_Security
{
    /**
     * File path for verification
     * @var string
     */
    protected $_filePath = 'app/etc/local.xml';

    /**
     * Time out for HTTP verification request
     * @var int
     */
    protected $_verificationTimeOut  = 2;

    /**
     * Check verification result and return true if system must to show notification message
     *
     * @return bool
     */
    protected function _canShowNotification()
    {
        if (Mage::app()->loadCache(self::VERIFICATION_RESULT_CACHE_KEY)) {
            return false;
        }
        if ($this->_isFileAccessible()) {
            return true;
        }
        $adminSessionLifetime = (int)Mage::getStoreConfig('admin/security/session_cookie_lifetime');
        if ($adminSessionLifetime < 60) {
            $adminSessionLifetime = false;
        }
        Mage::app()->saveCache(true, self::VERIFICATION_RESULT_CACHE_KEY, array(), $adminSessionLifetime);
        return false;
    }

    /**
     * If file is accessible return true or false
     *
     * @return bool
     */
    protected function _isFileAccessible()
    {
        $defaultUnsecureBaseURL = (string) Mage::getConfig()->getNode('default/' . Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL);

        $http = new Varien_Http_Adapter_Curl();
        $http->setConfig(array('timeout' => $this->_verificationTimeOut));
        $http->write(Zend_Http_Client::HEAD, $defaultUnsecureBaseURL . $this->_filePath);
        $responseBody = $http->read();
        $responseCode = Zend_Http_Response::extractCode($responseBody);
        $http->close();

        return $responseCode == 200;
    }

    protected function _toHtml()
    {
        if (!$this->_canShowNotification()) {
            return '';
        }
        return Mage_Adminhtml_Block_Template::_toHtml();
    }
}
