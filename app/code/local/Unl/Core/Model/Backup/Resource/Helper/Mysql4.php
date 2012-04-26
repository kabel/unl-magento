<?php

class Unl_Core_Model_Backup_Resource_Helper_Mysql4 extends Mage_Backup_Model_Resource_Helper_Mysql4
{
    public function getTableReferences($tableName)
    {
        $adapter = $this->_getReadAdapter();
        $select = $select = $this->_getReferenceSelect('REFERENCED_TABLE_NAME', 'TABLE_NAME', $tableName);

        return $adapter->fetchCol($select);
    }

    public function getTablesReferenced($tableName)
    {
        $adapter = $this->_getReadAdapter();
        $select = $this->_getReferenceSelect('TABLE_NAME', 'REFERENCED_TABLE_NAME', $tableName);

        return $adapter->fetchCol($select);
    }

    /**
     * Returns a select statement from the MySQL Information Schema
     * that will fetch reference constraint information
     *
     * @param Varien_Db_Adapter_Interface $adapter
     * @param string $col The column to fetch
     * @param string $cond The conditional column to match the $tableName to
     * @param string $tableName
     * @return Varien_Db_Select
     */
    protected function _getReferenceSelect($col, $cond, $tableName)
    {
        $dbConfig = $this->_getReadAdapter()->getConfig();
        $adapter = $this->_getReadAdapter();

        return $adapter->select()
            ->distinct()
            ->from('REFERENTIAL_CONSTRAINTS', $col, 'INFORMATION_SCHEMA')
            ->where('CONSTRAINT_SCHEMA = ?', $dbConfig['dbname'])
            ->where($cond . ' = ?', $tableName);
    }

    /**
     * Turn on repeatable read mode
     */
    public function turnOnRepeatableReadMode()
    {
        $this->_getReadAdapter()->query('SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ');
    }
}
