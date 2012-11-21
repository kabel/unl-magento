<?php

class Unl_Spam_Model_Resource_Sfs_Cache extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_spam/sfs_cache', 'cache_id');
    }
}
