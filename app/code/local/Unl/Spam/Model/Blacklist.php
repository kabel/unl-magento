<?php

class Unl_Spam_Model_Blacklist extends Unl_Spam_Model_RemoteAddrAbstract
{
    const RESPONSE_TYPE_403         = 1;
    const RESPONSE_TYPE_403_SPARSE  = 2;
    const RESPONSE_TYPE_404         = 3;
    const RESPONSE_TYPE_503         = 4;

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

    public function getCidrBitCount()
    {
        if (null === $this->getCidrBits()) {
            $mask = $this->getCidrMask();

            if (empty($mask)) {
                return null;
            }

            $count = Mage::helper('unl_spam')->getCidrBits($mask);
            $this->setCidrBits($count);
        }

        return $this->getCidrBits();
    }
}
