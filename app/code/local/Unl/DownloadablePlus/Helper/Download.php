<?php

class Unl_DownloadablePlus_Helper_Download extends Mage_Downloadable_Helper_Download
{
    const LINK_TYPE_CALLBACK = 'callback';
    const LINK_TYPE_REDIRECT = 'redirect';

    protected $_urlResponse;

    protected $_urlSecret;

    protected function _getHandle()
    {
        if (!$this->_resourceFile) {
            Mage::throwException(Mage::helper('downloadable')->__('Please set resource file and link type.'));
        }

        if (is_null($this->_handle)) {
            if ($this->_linkType == self::LINK_TYPE_URL) {
                $session = Mage::getSingleton('customer/session');

                $client = new Zend_Http_Client($this->_resourceFile, array(
                    'maxredirects' => 0,
                    'useragent' => 'Magento ver/' . Mage::getVersion(),
                    'timeout' => 30,
                    'storeresponse' => false,
                    'adapter' => 'Zend_Http_Client_Adapter_Curl',
                ));

                if ($this->_urlSecret) {
                    $client->setHeaders('X-Magento-Secret', $this->getHMAC($this->_resourceFile, $this->_urlSecret));
                }

                $this->_resourceFile = $client->getUri()->getPath();

                $client->setStream();
                $client->setHeaders('Accept-encoding', 'identity');

                try {
                    $session->unlock();
                    /* @var $response Zend_Http_Response_Stream */
                    $this->_urlResponse = $response = $client->request('GET');
                    $this->_handle = $response->getStream();
                    $this->_urlHeaders = array_change_key_case($response->getHeaders(), CASE_LOWER);

                    if ($response->getStatus() != 200) {
                        Mage::throwException(Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.'));
                    }
                } catch (Zend_Http_Client_Exception $e) {
                    Mage::logException($e);
                    Mage::throwException(Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.'));
                }

                $session->lock();
            }
            else {
                return parent::_getHandle();
            }
        }
        return $this->_handle;
    }

    public function setUrlSecret($secret)
    {
        $this->_urlSecret = $secret;
    }

    public function getHMAC($data, $key)
    {
        return 'sha1=' . hash_hmac('sha1', $data, $key);
    }
}
