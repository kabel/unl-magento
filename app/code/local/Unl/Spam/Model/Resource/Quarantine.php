<?php

class Unl_Spam_Model_Resource_Quarantine extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_spam/quarantine', 'quarantine_id');
    }
}
