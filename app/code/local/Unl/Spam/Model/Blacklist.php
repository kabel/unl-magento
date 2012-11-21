<?php

class Unl_Spam_Model_Blacklist extends Unl_Spam_Model_RemoteAddrAbstract
{
    const RESPONSE_TYPE_403 = 1;
    const RESPONSE_TYPE_503 = 2;

    protected function _construct()
    {
        $this->_init('unl_spam/blacklist');
    }

    protected function _beforeSave()
    {
        if (is_null($this->getData('created_at'))) {
            $this->setData('created_at', Mage::getSingleton('core/date')->gmtDate());
        }
        return parent::_beforeSave();
    }
}
