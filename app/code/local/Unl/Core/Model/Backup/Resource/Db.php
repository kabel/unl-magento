<?php

class Unl_Core_Model_Backup_Resource_Db extends Mage_Backup_Model_Resource_Db
{
    protected $_foreignKeyInfo = array();

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
        if (empty($this->_foreignKeyInfo) || empty($this->_foreignKeyInfo[$tableName])) {
            return array();
        }

        return $this->_foreignKeyInfo[$tableName]['references'];
    }

    public function getTablesReferenced($tableName)
    {
        if (empty($this->_foreignKeyInfo) || empty($this->_foreignKeyInfo[$tableName])) {
            return array();
        }

        return $this->_foreignKeyInfo[$tableName]['referenced'];
    }

    public function loadForeignKeysInfo($tables)
    {
        $adapter = Mage::getSingleton('core/resource')->getConnection('core_read');

        foreach ($tables as $table) {
            if (!isset($this->_foreignKeyInfo[$table])) {
                $this->_foreignKeyInfo[$table] = array(
                    'references' => array(),
                    'referenced' => array(),
                );
            }

            foreach($adapter->getForeignKeys($table) as $key) {
                $ref = $key['REF_TABLE_NAME'];
                if (!in_array($ref, $this->_foreignKeyInfo[$table]['references'])) {
                    $this->_foreignKeyInfo[$table]['references'][] = $ref;
                }

                if (!isset($this->_foreignKeyInfo[$ref])) {
                    $this->_foreignKeyInfo[$ref] = array(
                        'references' => array(),
                        'referenced' => array($table),
                    );
                } elseif (!in_array($table, $this->_foreignKeyInfo[$ref]['referenced'])) {
                    $this->_foreignKeyInfo[$ref]['referenced'][] = $table;
                }
            }
        }

        return $this->_foreignKeyInfo;
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
