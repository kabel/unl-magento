<?php

abstract class Unl_Spam_Model_RemoteAddrAbstract extends Mage_Core_Model_Abstract
{
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
        if ($this->getRemoteAddr()) {
            if ($toBinary) {
                $this->setRemoteAddr(inet_pton($this->getRemoteAddr()));
            } else {
                $this->setRemoteAddr(inet_ntop($this->getRemoteAddr()));
            }
        }

        return $this;
    }
}
