<?php

class Unl_Spam_Model_Sfs_Cache extends Mage_Core_Model_Abstract
{
    const XML_PATH_SFS_CACHE_TTL   = 'unl_spam/sfs/cache_ttl';
    const XML_PATH_SFS_CONFIDENCE  = 'unl_spam/sfs/confidence_threshold';

    protected function _construct()
    {
        $this->_init('unl_spam/sfs_cache');
    }

    protected function _beforeSave()
    {
        $this->swapRemoteAddr(true);
        return parent::_beforeSave();
    }

    protected function _afterSave()
    {
        $this->swapRemoteAddr();
        return parent::_afterSave();
    }

    protected function _afterLoad()
    {
        $this->swapRemoteAddr();
        return parent::_afterLoad();
    }

    public function swapRemoteAddr($toBinary = false)
    {
        if ($toBinary) {
            $this->setRemoteAddr(inet_pton($this->getRemoteAddr()));
        } else {
            $this->setRemoteAddr(inet_ntop($this->getRemoteAddr()));
        }

        return $this;
    }

    public function isValid()
    {
        if (!$this->getExpiresAt()) {
            return false;
        }

        $now = Mage::getSingleton('core/date')->gmtTimestamp();
        $valid = $now < Mage::getSingleton('core/date')->gmtTimestamp($this->getExpiresAt());

        return $valid;
    }

    public function isSpam()
    {
        $threshold = Mage::getStoreConfig(self::XML_PATH_SFS_CONFIDENCE);

        if ($this->getConfidence() >= $threshold) {
            return true;
        }

        return false;
    }

    public function fetch()
    {
        if ($this->getRemoteAddr() == '') {
            return false;
        }

        $client = new Zend_Http_Client('http://www.stopforumspam.com/api', array(
            'maxredirects' => 0,
            'useragent' => 'Magento ver/' . Mage::getVersion(),
            'timeout' => 3,
        ));

        $client->setParameterGet(array(
            'ip' => $this->getRemoteAddr(),
            'f'  => 'serial',
        ));

        $response = $client->request();
        $data = unserialize($response->getBody());

        if ($data) {
            $this->setExpiresAt(Mage::getSingleton('core/date')->gmtDate(time() + (Mage::getStoreConfig(self::XML_PATH_SFS_CACHE_TTL) * 60)));
            $this->setConfidence(isset($data['ip']['confidence']) ? $data['ip']['confidence'] : null);
            $this->setAppears($data['ip']['appears']);
            $this->save();
        }

        return true;
    }
}
