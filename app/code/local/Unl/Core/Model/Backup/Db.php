<?php

class Unl_Core_Model_Backup_Db extends Mage_Backup_Model_Db
{
    public function __construct()
    {
        $this->_ignoreDataTablesList[] = 'unl_core/tax_boundary';
    }
}
