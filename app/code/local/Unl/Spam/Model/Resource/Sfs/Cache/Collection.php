<?php

class Unl_Spam_Model_Resource_Sfs_Cache_Collection extends Unl_Spam_Model_Resource_Collection_RemoteAddrAbstract
{
    protected function _construct()
    {
        $this->_init('unl_spam/sfs_cache');
    }
}
