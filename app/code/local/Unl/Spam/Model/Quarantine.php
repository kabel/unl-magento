<?php

class Unl_Spam_Model_Quarantine extends Unl_Spam_Model_RemoteAddrAbstract
{
    protected function _construct()
    {
        $this->_init('unl_spam/quarantine');
    }

    public function isValid()
    {
        $now = Mage::getSingleton('core/date')->gmtTimestamp();
        $valid = $now < Mage::getSingleton('core/date')->gmtTimestamp($this->getExpiresAt());

        return $valid;
    }
}
