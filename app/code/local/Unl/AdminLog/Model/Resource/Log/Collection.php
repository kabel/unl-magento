<?php

class Unl_AdminLog_Model_Resource_Log_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_adminlog/log');
    }

    public function addArchivedFilter($flag = false)
    {
        $this->addFieldToFilter('is_archived', $flag);
        return $this;
    }
}
