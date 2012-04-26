<?php

class Unl_Core_Model_Backup_Resource_Db extends Mage_Backup_Model_Resource_Db
{
    /* Overrides
     * @see Mage_Backup_Model_Resource_Db::getTableCreateSql()
     * by fixing accidental var assignment
     */
    public function getTableCreateSql($tableName, $withForeignKeys = false)
    {
        return Mage::getResourceHelper('backup')->getTableCreateSql($tableName, $withForeignKeys);
    }

    public function getTableReferences($tableName)
    {
        return Mage::getResourceHelper('backup')->getTableReferences($tableName);
    }

    public function getTablesReferenced($tableName)
    {
        return Mage::getResourceHelper('backup')->getTablesReferenced($tableName);
    }

    public function beginTransaction()
    {
        $this->_write->beginTransaction();
        return $this;
    }

    public function commitTransaction()
    {
        $this->_write->commit();
        return $this;
    }

    public function turnOnSerializableMode()
    {
        Mage::getResourceHelper('backup')->turnOnSerializableMode();
        return $this;
    }

    public function turnOnRepeatableReadMode()
    {
        Mage::getResourceHelper('backup')->turnOnRepeatableReadMode();
    }
}
