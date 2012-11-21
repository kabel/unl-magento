<?php

class Unl_Spam_Model_Resource_Blacklist_Collection extends Unl_Spam_Model_Resource_Collection_RemoteAddrAbstract
{
    protected function _construct()
    {
        $this->_init('unl_spam/blacklist');
    }
}
