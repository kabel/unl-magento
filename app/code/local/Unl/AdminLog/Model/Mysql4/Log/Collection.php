<?php

class Unl_AdminLog_Model_Mysql4_Log_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
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
