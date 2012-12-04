<?php

class Unl_Core_Model_Backup_Resource_Helper_Mysql4 extends Mage_Backup_Model_Resource_Helper_Mysql4
{
    /**
     * Turn on repeatable read mode
     */
    public function turnOnRepeatableReadMode()
    {
        $this->_getReadAdapter()->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    }
}
