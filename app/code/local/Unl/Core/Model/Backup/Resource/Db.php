<?php

class Unl_Core_Model_Backup_Resource_Db extends Mage_Backup_Model_Resource_Db
{
    /**
     * The list of tables to exclude from the backup
     *
     * @var array
     */
    protected $_ignoreTables = array();

    public function __construct()
    {
        parent::__construct();

        $this->ignoreTable('unl_core/tax_boundary');
    }

    /**
     * Add a resource table to the array of tables to ignore
     *
     * @param string $table
     * @return Unl_Core_Model_Backup_Resource_Db
     */
    public function ignoreTable($table)
    {
        $tableName = Mage::getSingleton('core/resource')->getTableName($table);

        if (!in_array($tableName, $this->_ignoreTables)) {
            $this->_ignoreTables[] = $tableName;
        }

        return $this;
    }

    /* Extends
     * @see Mage_Backup_Model_Resource_Db::getTables()
     * by removing the listed tables found in <code>_ignoreTables</code>
     */
    public function getTables()
    {
        $tables = parent::getTables();

        foreach ($tables as $k => $table) {
            if (in_array($table, $this->_ignoreTables)) {
                unset($tables[$k]);
            }
        }

        return $tables;
    }

    /* Extends
     * @see Mage_Backup_Model_Resource_Db::beginTransaction()
     * by adding a max_execution_time extension
     */
    public function beginTransaction()
    {
        ini_set('max_execution_time', 0);
        return parent::beginTransaction();
    }
}
